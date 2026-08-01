/**
 * OCOP STORYTELLING MAP ENGINE - DONG ANH OCOP & HERITAGE PRODUCTS
 * Leaflet.js | GSAP | Web Speech API TTS | "ĐA" Map Pattern & Domino LED Grand Finale
 */

class OcopStoryteller {
    constructor() {
        this.map = null;
        this.currentData = null;
        this.activeStep = 0;
        this.isSkipped = false;
        this.isBusy = false;
        this.sessionId = 0;
        this.markers = [];
        this.daPinMarkers = [];
        this.speechSynth = 'speechSynthesis' in window ? window.speechSynthesis : null;
    }

    initMap() {
        if (this.map) return;

        const mapContainer = document.getElementById('ocopStoryMap');
        if (!mapContainer) return;

        this.map = L.map('ocopStoryMap', {
            zoomControl: false,
            attributionControl: false,
            fadeAnimation: true,
            zoomAnimation: true
        }).setView([21.135, 105.865], 12);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            subdomains: 'abcd'
        }).addTo(this.map);
    }

    initSparkleCanvas() {
        const canvas = document.getElementById('ocopSparkleCanvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        const particles = [];
        const particleCount = 60;
        const colors = ['#f59e0b', '#fbbf24', '#10b981', '#34d399', '#d97706'];

        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                size: Math.random() * 3.5 + 1,
                color: colors[Math.floor(Math.random() * colors.length)],
                speedY: -(Math.random() * 0.6 + 0.2),
                speedX: (Math.random() - 0.5) * 0.4,
                alpha: Math.random(),
                fade: Math.random() * 0.02 + 0.005
            });
        }

        const animateSparkles = () => {
            const modal = document.getElementById('ocopStorytellingModal');
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
        this.sessionId++;
        this.isSkipped = true;
        this.isBusy = false;
        this.activeStep = 0;

        if (this.speechSynth) this.speechSynth.cancel();

        this.clearMarkers();

        const modal = document.getElementById('ocopStorytellingModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }

        const finaleOverlay = document.getElementById('ocopGrandFinaleOverlay');
        if (finaleOverlay) finaleOverlay.classList.remove('active');

        const intro = document.getElementById('ocopIntroScreen');
        if (intro) intro.classList.add('hidden');
    }

    clearMarkers() {
        this.markers.forEach(m => this.map && this.map.removeLayer(m));
        this.daPinMarkers.forEach(m => this.map && this.map.removeLayer(m));
        this.markers = [];
        this.daPinMarkers = [];
    }

    typeWriter(textElementId, text, speed = 18) {
        return new Promise(resolve => {
            const el = document.getElementById(textElementId);
            if (!el) return resolve();

            el.innerHTML = '';
            let i = 0;

            const timer = setInterval(() => {
                if (this.isSkipped) {
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

    speakNarrative(text) {
        if (this.speechSynth) {
            this.speechSynth.cancel();
        }
        return;
    }

    /**
     * Compute "ĐA" (Đông Anh) Map Coordinates for N products
     * Generates a grid pattern forming the letters Đ and A across Dong Anh map
     */
    generateDaCoordinates(count) {
        const centerLat = 21.135;
        const centerLng = 105.868;
        const latScale = 0.035;
        const lngScale = 0.055;

        // Normalized relative (x, y) points forming letter Đ and letter A
        const letterPoints = [
            // Letter Đ (Left side: x from -0.45 to -0.05)
            { x: -0.45, y: 0.4 }, { x: -0.45, y: 0.2 }, { x: -0.45, y: 0.0 }, { x: -0.45, y: -0.2 }, { x: -0.45, y: -0.4 },
            { x: -0.55, y: 0.0 }, { x: -0.45, y: 0.0 }, { x: -0.35, y: 0.0 }, // Crossbar of Đ
            { x: -0.35, y: 0.4 }, { x: -0.20, y: 0.38 }, { x: -0.12, y: 0.2 }, { x: -0.12, y: -0.2 }, { x: -0.20, y: -0.38 }, { x: -0.35, y: -0.4 },

            // Letter A (Right side: x from 0.05 to 0.45)
            { x: 0.08, y: -0.4 }, { x: 0.15, y: -0.1 }, { x: 0.25, y: 0.4 }, // Left leg of A
            { x: 0.35, y: -0.1 }, { x: 0.42, y: -0.4 }, // Right leg of A
            { x: 0.15, y: -0.05 }, { x: 0.25, y: -0.05 }, { x: 0.35, y: -0.05 } // Crossbar of A
        ];

        const result = [];
        for (let i = 0; i < count; i++) {
            const pt = letterPoints[i % letterPoints.length];
            const jitterLat = (Math.floor(i / letterPoints.length) * 0.003);
            const jitterLng = (Math.floor(i / letterPoints.length) * 0.003);
            result.push({
                lat: centerLat + (pt.y * latScale) + jitterLat,
                lng: centerLng + (pt.x * lngScale) + jitterLng
            });
        }

        return result;
    }

    /**
     * Main Entry: Start Full OCOP Products Presentation (Sequential Flow + Domino LED Finale)
     */
    async startFullOcopTour() {
        this.closeAndResetModal();

        const session = ++this.sessionId;
        this.isBusy = true;
        this.isSkipped = false;
        this.activeStep = 0;

        // Ingest DB products or fallback
        let products = window.DB_OCOP_PRODUCTS || [];
        if (!products || products.length === 0) {
            products = [
                {
                    name: "Đông Trùng Hạ Thảo Tươi KOVI",
                    star_rating: "4 sao OCOP",
                    price: "80.000đ",
                    eatery_name: "HTX Nông Nghiệp Dược Liệu KOVI",
                    eatery_address: "Thôn Lộc Hà, Xã Đông Anh, Hà Nội",
                    lat: 21.092694,
                    lng: 105.883345,
                    image: "https://media.xadonganh.com/ocop/1785483918_Uf4r3vOz.webp",
                    description: "Nuôi cấy công nghệ sinh học khép kín đạt tiêu chuẩn HACCP và GMP. Giữ trọn hoạt chất sinh học Cordycepin & Adenosine tự nhiên quý giá."
                },
                {
                    name: "Tượng Phật Đồ Gỗ Mỹ Nghệ Vân Hà",
                    star_rating: "4 sao OCOP",
                    price: "12.500.000đ",
                    eatery_name: "Làng Nghề Mộc Vân Hà",
                    eatery_address: "Thôn Vân Hà, Xã Đông Anh, Hà Nội",
                    lat: 21.162000,
                    lng: 105.895000,
                    image: "https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80",
                    description: "Tác phẩm điêu khắc gỗ thủ công độc bản bởi Nghệ nhân Bàn tay vàng, giữ trọn hồn quê và bản sắc di sản văn hóa Việt."
                },
                {
                    name: "Bánh Gạo Lứt Hoàng Chiến Thắng",
                    star_rating: "4 sao OCOP",
                    price: "30.000đ",
                    eatery_name: "Công ty TNHH Hoàng Chiến Thắng",
                    eatery_address: "Xã Đông Anh, Hà Nội",
                    lat: 21.069422,
                    lng: 105.867833,
                    image: "https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80",
                    description: "Sản phẩm bánh dinh dưỡng thơm ngon từ hạt gạo lứt hữu cơ bãi bồi, không chất bảo quản, an toàn tuyệt đối cho sức khỏe."
                },
                {
                    name: "Rượu Gạo Nếp Long Tửu Thạo Loan",
                    star_rating: "3 sao OCOP",
                    price: "150.000đ",
                    eatery_name: "HKD Thạo Loan",
                    eatery_address: "Xã Đông Anh, Hà Nội",
                    lat: 21.081878,
                    lng: 105.847897,
                    image: "https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?auto=format&fit=crop&w=800&q=80",
                    description: "Hạ thổ theo công nghệ truyền thống 365 ngày, ngấm vị nồng nàn thơm dẻo của nếp cái hoa vàng cổ truyền."
                }
            ];
        }

        // Use real coordinates of the products to prevent jumping, falling back to DA coordinates if empty
        const generatedCoords = this.generateDaCoordinates(products.length);
        const daCoords = [];
        products.forEach((p, idx) => {
            p.daLat = p.lat || generatedCoords[idx].lat;
            p.daLng = p.lng || generatedCoords[idx].lng;
            daCoords.push({ lat: p.daLat, lng: p.daLng });
        });

        this.currentProducts = products;

        const modal = document.getElementById('ocopStorytellingModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('active');
        }

        this.initMap();
        this.initSparkleCanvas();
        this.renderTimeline();

        // 1. Intro Screen
        const intro = document.getElementById('ocopIntroScreen');
        if (intro) {
            document.getElementById('ocopIntroTitle').innerHTML = `HÀNH TRÌNH TỔNG THỂ<br><span class="story-intro-highlight">SỐ HÓA 100% SẢN PHẨM OCOP ĐÔNG ANH</span>`;
            document.getElementById('ocopIntroSubtitle').innerText = `Khám phá trực quan lần lượt ${products.length} sản phẩm OCOP & Làng nghề di sản được xếp đặt hình chữ "ĐA" trên bản đồ`;
            intro.classList.remove('hidden');

            await new Promise(r => setTimeout(r, 2600));
            if (this.isSkipped || session !== this.sessionId) return;
            intro.classList.add('hidden');
        }

        // 2. Play Product Sequence
        for (let i = 0; i < products.length; i++) {
            if (this.isSkipped || session !== this.sessionId) break;
            await this.playProductStep(i, session);
        }

        // 3. Grand Finale: Domino LED Effect & Slogan Overlay Card
        if (!this.isSkipped && session === this.sessionId) {
            await this.triggerDominoLedEffect();
        }
    }

    startStory(slug) {
        return this.startFullOcopTour();
    }

    renderTimeline() {
        const container = document.getElementById('ocopTimelineList');
        const dotsWrap = document.getElementById('ocopProgressDots');
        if (!container || !this.currentProducts) return;

        container.innerHTML = '';
        if (dotsWrap) dotsWrap.innerHTML = '';

        this.currentProducts.forEach((p, idx) => {
            const item = document.createElement('div');
            item.className = `story-timeline-item ${idx === 0 ? 'active' : ''}`;
            item.innerHTML = `
                <div class="story-timeline-num">${idx + 1}</div>
                <div class="story-timeline-text">
                    <h4>${p.star_rating || 'OCOP 4 Sao'}</h4>
                    <p>${p.name}</p>
                </div>
            `;
            item.onclick = () => this.playProductStep(idx, this.sessionId);
            container.appendChild(item);

            if (dotsWrap) {
                const dot = document.createElement('div');
                dot.className = `story-progress-dot ${idx === 0 ? 'active' : ''}`;
                dotsWrap.appendChild(dot);
            }
        });
    }

    async playProductStep(index, session) {
        if (!this.currentProducts || !this.currentProducts[index]) return;
        const prod = this.currentProducts[index];
        this.activeStep = index;

        // Update Timeline & Dots UI
        const items = document.querySelectorAll('#ocopTimelineList .story-timeline-item');
        items.forEach((it, idx) => {
            it.classList.toggle('active', idx === index);
        });

        const dots = document.querySelectorAll('#ocopProgressDots .story-progress-dot');
        dots.forEach((dt, idx) => {
            dt.classList.toggle('active', idx === index);
        });

        document.getElementById('ocopPhaseLabel').innerText = `SẢN PHẨM ${index + 1}/${this.currentProducts.length}: ${prod.name.toUpperCase()}`;

        // 1. Leaflet Map FlyTo real coordinates or ĐA coordinates
        const targetLat = prod.lat || prod.daLat;
        const targetLng = prod.lng || prod.daLng;

        if (this.map && targetLat && targetLng) {
            this.map.flyTo([targetLat, targetLng], 15, {
                duration: 1.8,
                easeLinearity: 0.3
            });

            // Clean temporary active focus marker
            if (this.activeFocusMarker) {
                this.map.removeLayer(this.activeFocusMarker);
            }

            // Create focused product image popup marker
            const focusIcon = L.divIcon({
                className: 'ocop-active-focus-marker',
                html: `
                    <div style="background: rgba(6, 78, 59, 0.95); border: 2px solid #fbbf24; border-radius: 14px; padding: 6px; box-shadow: 0 10px 25px rgba(217, 119, 6, 0.6); display: flex; align-items: center; gap: 8px; width: 220px;">
                        <img src="${prod.image}" style="width: 48px; height: 48px; border-radius: 8px; object-fit: cover; border: 1px solid #fbbf24; flex-shrink: 0;">
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-size: 11px; font-weight: 800; color: #fbbf24;">${prod.star_rating}</div>
                            <div style="font-size: 12px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${prod.name}</div>
                        </div>
                    </div>
                `,
                iconSize: [220, 60],
                iconAnchor: [110, 30]
            });
            this.activeFocusMarker = L.marker([targetLat, targetLng], { icon: focusIcon }).addTo(this.map);
        }

        // 2. Right Side Glass Card Info
        document.getElementById('ocopCardImage').src = prod.image;
        document.getElementById('ocopCardBadge').innerText = prod.star_rating || 'Sản Phẩm OCOP';
        document.getElementById('ocopCardTitle').innerText = prod.name;
        document.getElementById('ocopCardAddress').innerText = `📍 ${prod.eatery_name} (${prod.eatery_address})`;
        document.getElementById('ocopStatRating').innerText = prod.star_rating;
        document.getElementById('ocopStatYear').innerText = prod.price;

        const artisanSec = document.getElementById('ocopArtisanSection');
        artisanSec.style.display = 'block';
        document.getElementById('ocopArtisanName').innerText = `Chủ thể: ${prod.eatery_name}`;

        const prodSec = document.getElementById('ocopProductsSection');
        prodSec.style.display = 'none';

        // 3. Voice & Narrative Typing
        const narrativeText = `Sản phẩm OCOP ${prod.name} đạt chứng nhận ${prod.star_rating}, do ${prod.eatery_name} sản xuất tại ${prod.eatery_address}. Mức giá niêm yết ${prod.price}. ${prod.description.substring(0, 180)}...`;
        
        this.speakNarrative(narrativeText);
        await this.typeWriter('ocopNarrativeText', narrativeText);

        // 4. Settle focus into a permanent pin forming letter "ĐA"
        if (this.map && prod.daLat && prod.daLng) {
            // Remove temporary active focus marker
            if (this.activeFocusMarker) {
                this.map.removeLayer(this.activeFocusMarker);
                this.activeFocusMarker = null;
            }

            const pinIcon = L.divIcon({
                className: 'ocop-da-marker',
                html: `<div id="daPin_${index}" style="background: linear-gradient(135deg, #d97706, #059669); color: #ffffff; font-weight: 800; font-size: 11px; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.4);">${index + 1}</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });
            const pinMarker = L.marker([prod.daLat, prod.daLng], { icon: pinIcon }).addTo(this.map);
            this.daPinMarkers.push(pinMarker);
        }

        await new Promise(r => setTimeout(r, 2200));
    }

    /**
     * Domino LED Light-Up Grand Finale
     * Sweeps a glowing LED wave through all map pins spelling out "ĐA"
     */
    async triggerDominoLedEffect() {
        if (this.speechSynth) this.speechSynth.cancel();

        // 1. Zoom out map to show full "ĐA" letter logo
        if (this.map) {
            this.map.flyTo([21.135, 105.868], 12.5, {
                duration: 2.2,
                easeLinearity: 0.25
            });
        }

        const cleanNarrative = "Sáng bừng hình tượng di sản Đông Anh! Đèn LED Domino đã thắp sáng toàn bộ bản đồ sản phẩm OCOP!";
        this.speakNarrative(cleanNarrative);
        this.typeWriter('ocopNarrativeText', cleanNarrative);

        // 2. Domino LED wave animation: Pin by pin activation
        for (let i = 0; i < this.daPinMarkers.length; i++) {
            if (this.isSkipped) break;
            const el = document.getElementById(`daPin_${i}`);
            if (el) {
                el.classList.add('led-active');
            }
            await new Promise(r => setTimeout(r, 120)); // Fast domino delay
        }

        await new Promise(r => setTimeout(r, 1500));

        // 3. Show Grand Finale Pop-up Announcement Card
        const finaleOverlay = document.getElementById('ocopGrandFinaleOverlay');
        if (finaleOverlay && !this.isSkipped) {
            finaleOverlay.classList.add('active');
        }
    }

    replayFullTour() {
        const finaleOverlay = document.getElementById('ocopGrandFinaleOverlay');
        if (finaleOverlay) finaleOverlay.classList.remove('active');
        this.startFullOcopTour();
    }
}

window.ocopStoryteller = new OcopStoryteller();

window.openOcopStoryteller = function(slug) {
    if (window.ocopStoryteller) {
        window.ocopStoryteller.startFullOcopTour();
    }
};

window.openOcopFullHeritageStory = function() {
    if (window.ocopStoryteller) {
        window.ocopStoryteller.startFullOcopTour();
    }
};
