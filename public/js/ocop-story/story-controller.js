/**
 * STORY CONTROLLER - OCOP STORYTELLING
 * Master Coordinator & Orchestrator for Cinematic OCOP Movie Experience
 * Left Sidebar Timeline, Pause/Resume, Prev/Next & Jump To Product Controls
 */

class OcopStoryController {
    constructor() {
        this.map = null;
        this.cameraCtrl = new window.OcopCameraController(null);
        this.markerCtrl = new window.OcopMarkerController(null);
        this.cardCtrl = new window.OcopCardController();
        this.animCtrl = new window.OcopAnimationController();
        this.products = [];
        this.currentIndex = 0;
        this.isBusy = false;
        this.isSkipped = false;
        this.isPaused = false;
        this.sessionId = 0;
        this.jumpRequestedIndex = -1;
    }

    closeAndReset() {
        this.sessionId++;
        this.isSkipped = true;
        this.isBusy = false;
        this.isPaused = false;
        this.jumpRequestedIndex = -1;

        if (this.markerCtrl) this.markerCtrl.clearAll();
        if (this.cardCtrl) this.cardCtrl.slideOutCard();

        const modal = document.getElementById('ocopStorytellingModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }

        const introOverlay = document.getElementById('ocopCinematicIntroOverlay');
        if (introOverlay) introOverlay.style.display = 'none';

        const sloganOverlay = document.getElementById('ocopSloganOverlay');
        if (sloganOverlay) sloganOverlay.style.display = 'none';
    }

    /**
     * Pause / Resume Playback Toggle
     */
    togglePause() {
        this.isPaused = !this.isPaused;
        const pauseBtn = document.getElementById('ocopPauseBtn');
        if (pauseBtn) {
            pauseBtn.innerHTML = this.isPaused ? '▶️ Tiếp tục' : '⏸️ Tạm dừng';
        }
    }

    /**
     * Jump to Previous Product
     */
    prevProduct() {
        if (this.currentIndex > 0) {
            this.jumpToProduct(this.currentIndex - 1);
        }
    }

    /**
     * Jump to Next Product
     */
    nextProduct() {
        if (this.currentIndex < this.products.length - 1) {
            this.jumpToProduct(this.currentIndex + 1);
        }
    }

    /**
     * Jump directly to product at given index
     */
    jumpToProduct(index) {
        if (index >= 0 && index < this.products.length) {
            this.jumpRequestedIndex = index;
            if (this.isPaused) {
                this.isPaused = false;
                const pauseBtn = document.getElementById('ocopPauseBtn');
                if (pauseBtn) pauseBtn.innerHTML = '⏸️ Tạm dừng';
            }
        }
    }

    /**
     * Helper to handle Pause state or waiting loop
     */
    async checkPauseOrWait(ms = 100) {
        while (this.isPaused && !this.isSkipped && this.jumpRequestedIndex === -1) {
            await new Promise(r => setTimeout(r, 100));
        }
    }

    /**
     * Render Left Sidebar Timeline List
     */
    renderLeftSidebarList(prods) {
        const listEl = document.getElementById('ocopTimelineList');
        if (!listEl) return;

        listEl.innerHTML = prods.map((p, idx) => `
            <div id="ocopTimelineItem_${idx}" class="ocop-timeline-item ${idx === 0 ? 'active' : ''}" onclick="window.ocopStoryController.jumpToProduct(${idx})">
                <div class="item-num">${idx + 1}</div>
                <div class="item-info">
                    <div class="item-name">${p.name}</div>
                    <div class="item-sub">${p.star_rating || 'OCOP 4 Sao'} • ${p.eatery_name}</div>
                </div>
            </div>
        `).join('');
    }

    /**
     * Highlight item i in Left Sidebar List & scroll into view
     */
    highlightSidebarItem(index) {
        this.products.forEach((_, idx) => {
            const el = document.getElementById(`ocopTimelineItem_${idx}`);
            if (el) {
                if (idx === index) {
                    el.classList.add('active');
                    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    el.classList.remove('active');
                }
            }
        });
    }

    /**
     * Start Full Cinematic Story Movie
     */
    async startMovie() {
        this.closeAndReset();

        const session = ++this.sessionId;
        this.isBusy = true;
        this.isSkipped = false;
        this.isPaused = false;
        this.jumpRequestedIndex = -1;

        // Ingest DB products or fallback
        let prods = window.DB_OCOP_PRODUCTS || [];
        if (!prods || prods.length === 0) {
            prods = [
                {
                    id: 1,
                    name: "Đông trùng hạ thảo khô 50g loại 1 xếp sợi",
                    star_rating: "4 sao OCOP",
                    price: "650.000đ",
                    eatery_name: "HTX Nông Nghiệp Dược Liệu KOVI",
                    eatery_address: "Thôn Lộc Hà, Xã Đông Anh, Hà Nội",
                    lat: 21.092694,
                    lng: 105.883345,
                    image: "https://media.xadonganh.com/ocop/1785485755_0EoKQRKz.webp",
                    description: "Hạt nấm đông trùng sấy thăng hoa -40°C bảo toàn 99% dược chất Cordycepin & Adenosine quý giá."
                },
                {
                    id: 2,
                    name: "Bộ Tranh Tứ Quý Gỗ Mỹ Nghệ Vân Hà",
                    star_rating: "4 sao OCOP",
                    price: "12.500.000đ",
                    eatery_name: "Làng Nghề Mộc Mỹ Nghệ Vân Hà",
                    eatery_address: "Thôn Vân Hà, Xã Đông Anh, Hà Nội",
                    lat: 21.162000,
                    lng: 105.895000,
                    image: "https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80",
                    description: "Đục chạm thủ công truyền đời bởi Nghệ nhân Bàn tay vàng, giữ trọn thần thái và văn hóa di sản."
                },
                {
                    id: 3,
                    name: "Bánh Gạo Lứt Hoàng Chiến Thắng",
                    star_rating: "4 sao OCOP",
                    price: "30.000đ",
                    eatery_name: "Công ty TNHH Hoàng Chiến Thắng",
                    eatery_address: "Xã Đông Anh, Hà Nội",
                    lat: 21.069422,
                    lng: 105.867833,
                    image: "https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80",
                    description: "Sản phẩm thực phẩm dinh dưỡng từ hạt gạo lứt bãi bồi sông Hồng, tiêu chuẩn ISO 22000."
                }
            ];
        }

        this.products = prods;

        // Use real coordinates of the products to prevent jumping, falling back to DA coordinates if empty
        const generatedCoords = this.markerCtrl.generateDaCoordinates(prods.length);
        const daCoords = [];
        prods.forEach((p, idx) => {
            p.daLat = p.lat || generatedCoords[idx].lat;
            p.daLng = p.lng || generatedCoords[idx].lng;
            daCoords.push({ lat: p.daLat, lng: p.daLng });
        });

        // Open Modal and reset skip button text
        const skipBtn = document.querySelector('.story-btn-skip');
        if (skipBtn) {
            skipBtn.innerHTML = '<span>⏭️</span> Bỏ qua phim';
        }
        const modal = document.getElementById('ocopStorytellingModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('active');
        }

        // Render Left Sidebar List
        this.renderLeftSidebarList(prods);

        // Init Map & Render "ĐA" Outline Grid immediately
        const mapContainer = document.getElementById('ocopStoryMap');
        if (mapContainer) {
            if (!this.map) {
                if (mapContainer._leaflet_id) {
                    mapContainer._leaflet_id = null;
                }
                try {
                    this.map = L.map('ocopStoryMap', {
                        zoomControl: false,
                        attributionControl: false,
                        fadeAnimation: true,
                        zoomAnimation: true
                    }).setView([21.135, 105.868], 12);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        maxZoom: 19,
                        subdomains: 'abcd'
                    }).addTo(this.map);
                } catch(e) {
                    console.warn('Leaflet init note:', e);
                }
            }
            if (this.map) {
                this.cameraCtrl.setMap(this.map);
                this.markerCtrl.setMap(this.map);
                setTimeout(() => {
                    this.map.invalidateSize();
                }, 150);
            }
        }

        // Render faint glowing outline dots for "ĐA" shape on map
        this.markerCtrl.renderDaOutlineGrid(daCoords);

        // Step 1: Cinematic Intro & Countdown 3-2-1
        await this.animCtrl.playCinematicIntro(prods.length);
        if (this.isSkipped || session !== this.sessionId) return;

        // Step 2: Loop through each product chapter
        let prevLat = null;
        let prevLng = null;
        let i = 0;

        while (i < prods.length) {
            if (this.isSkipped || session !== this.sessionId) break;

            // Check if user requested jump to a specific product
            if (this.jumpRequestedIndex !== -1) {
                i = this.jumpRequestedIndex;
                this.jumpRequestedIndex = -1;
            }

            this.currentIndex = i;
            this.highlightSidebarItem(i);

            const p = prods[i];
            const curLat = p.daLat;
            const curLng = p.daLng;

            // Draw GPS line
            if (prevLat && prevLng) {
                this.cameraCtrl.drawGpsRoute(prevLat, prevLng, curLat, curLng);
            }

            // Flycam animation (1.8s)
            await this.cameraCtrl.flyToTarget(curLat, curLng, 14.5, 1.8);
            if (this.isSkipped || session !== this.sessionId) break;

            // Focus Marker Glow/Pulse
            this.markerCtrl.showFocusMarker(curLat, curLng, p);

            // Slide in Glassmorphism Card
            await this.cardCtrl.slideInCard(p, i, prods.length);
            if (this.isSkipped || session !== this.sessionId) break;

            // Display time (3.2s or wait if paused)
            let elapsed = 0;
            const displayTime = 3200;
            while (elapsed < displayTime && !this.isSkipped && session === this.sessionId && this.jumpRequestedIndex === -1) {
                await new Promise(r => setTimeout(r, 100));
                if (!this.isPaused) {
                    elapsed += 100;
                }
            }

            if (this.isSkipped || session !== this.sessionId) break;

            // If user jumped to another product during display time, loop immediately
            if (this.jumpRequestedIndex !== -1) {
                await this.cardCtrl.slideOutCard();
                this.markerCtrl.settleDaPin(curLat, curLng, i, prods.length);
                prevLat = curLat;
                prevLng = curLng;
                continue;
            }

            // Slide out Card & settle pin on "ĐA" grid
            await this.cardCtrl.slideOutCard();
            this.markerCtrl.settleDaPin(curLat, curLng, i, prods.length);

            prevLat = curLat;
            prevLng = curLng;
            i++;
        }

        // Step 3: Grand Finale - Zoom out to see GIANT "ĐA" shape & Domino LED Wave (Outro removed)
        if (!this.isSkipped && session === this.sessionId) {
            // Update skip button to Close
            const skipBtn = document.querySelector('.story-btn-skip');
            if (skipBtn) {
                skipBtn.innerHTML = '<span>❌</span> Đóng';
            }
            this.markerCtrl.morphToDaLetters(this.products.length);
            await this.cameraCtrl.flyToTarget(21.135, 105.868, 11.2, 2.5);
            await this.markerCtrl.triggerDominoLedEffect();
        }
    }
}

window.ocopStoryController = new OcopStoryController();

window.openOcopFullHeritageStory = function() {
    if (window.ocopStoryController) {
        window.ocopStoryController.startMovie();
    }
};

window.openOcopStoryteller = function() {
    if (window.ocopStoryController) {
        window.ocopStoryController.startMovie();
    }
};
