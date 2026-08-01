/**
 * MARKER CONTROLLER - OCOP STORYTELLING
 * Marker Focus (Glow, Halo, Pulse, Ripple), Background Dimming, "ĐA" Outline Grid & Domino LED Effect
 */

class OcopMarkerController {
    constructor(map) {
        this.map = map;
        this.activeFocusMarker = null;
        this.daPinMarkers = [];
        this.outlineMarkers = [];
    }

    setMap(map) {
        this.map = map;
    }

    clearAll() {
        if (this.activeFocusMarker && this.map) {
            this.map.removeLayer(this.activeFocusMarker);
            this.activeFocusMarker = null;
        }
        this.daPinMarkers.forEach(m => this.map && this.map.removeLayer(m));
        this.outlineMarkers.forEach(m => this.map && this.map.removeLayer(m));
        this.daPinMarkers = [];
        this.outlineMarkers = [];
    }

    /**
     * Render faint glowing outline dots for the entire "ĐA" layout right from the start
     */
    renderDaOutlineGrid(daCoords) {
        if (!this.map || !daCoords) return;

        this.outlineMarkers.forEach(m => this.map && this.map.removeLayer(m));
        this.outlineMarkers = [];

        daCoords.forEach((coord, idx) => {
            const iconHtml = `<div id="daOutline_${idx}" class="ocop-da-outline-dot"></div>`;
            const icon = L.divIcon({
                className: 'ocop-da-outline-div',
                html: iconHtml,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });
            const marker = L.marker([coord.lat, coord.lng], { icon }).addTo(this.map);
            this.outlineMarkers.push(marker);
        });
    }

    /**
     * Create Glowing Pulsing Active Product Marker on Map
     */
    showFocusMarker(lat, lng, product) {
        if (!this.map) return;

        if (this.activeFocusMarker) {
            this.map.removeLayer(this.activeFocusMarker);
        }

        const iconHtml = `
            <div class="ocop-hero-marker-wrap">
                <div class="ocop-marker-halo"></div>
                <div class="ocop-marker-ripple"></div>
                <div class="ocop-marker-card">
                    <img src="${product.image}" class="ocop-marker-img">
                    <div class="ocop-marker-badge">${product.star_rating || 'OCOP 4 SAO'}</div>
                </div>
            </div>
        `;

        const icon = L.divIcon({
            className: 'ocop-active-focus-div',
            html: iconHtml,
            iconSize: [80, 80],
            iconAnchor: [40, 40]
        });

        this.activeFocusMarker = L.marker([lat, lng], { icon }).addTo(this.map);
    }

    /**
     * Settle active focus marker into a permanent pin forming letter "ĐA"
     * Replaces the i-th outline dot immediately with a glowing pin node
     */
    settleDaPin(daLat, daLng, index, totalCount) {
        if (this.activeFocusMarker && this.map) {
            this.map.removeLayer(this.activeFocusMarker);
            this.activeFocusMarker = null;
        }

        // Hide/Remove outline dot at this index if present
        if (this.outlineMarkers[index] && this.map) {
            this.map.removeLayer(this.outlineMarkers[index]);
        }

        const pinHtml = `
            <div id="daPin_${index}" class="ocop-da-pin-node">
                <span>${index + 1}</span>
            </div>
        `;

        const icon = L.divIcon({
            className: 'ocop-da-pin-div',
            html: pinHtml,
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        const marker = L.marker([daLat, daLng], { icon }).addTo(this.map);
        this.daPinMarkers[index] = marker;
    }

    /**
     * Clear all current pins and render the complete "ĐA" letter constellation
     */
    morphToDaLetters(totalCount = 33) {
        if (!this.map) return;

        // 1. Clear all active focus and existing pins
        if (this.activeFocusMarker) {
            this.map.removeLayer(this.activeFocusMarker);
            this.activeFocusMarker = null;
        }
        this.daPinMarkers.forEach(m => m && this.map.removeLayer(m));
        this.outlineMarkers.forEach(m => m && this.map.removeLayer(m));
        this.daPinMarkers = [];
        this.outlineMarkers = [];

        // 2. Generate ĐA letter coordinates
        const daCoords = this.generateDaCoordinates(totalCount);

        // 3. Render all 33 pins forming the letters Đ and A
        daCoords.forEach((coord, index) => {
            const pinHtml = `
                <div id="daPin_${index}" class="ocop-da-pin-node" style="transform: scale(0); opacity: 0;">
                    <span>${index + 1}</span>
                </div>
            `;
            const icon = L.divIcon({
                className: 'ocop-da-pin-div',
                html: pinHtml,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });
            const marker = L.marker([coord.lat, coord.lng], { icon }).addTo(this.map);
            this.daPinMarkers[index] = marker;
        });

        // 4. GSAP Stagger Drop-in Animation for the ĐA pins
        setTimeout(() => {
            gsap.fromTo(".ocop-da-pin-node", 
                { scale: 0, opacity: 0, y: -60, rotation: -90 },
                { 
                    scale: 1, 
                    opacity: 1, 
                    y: 0, 
                    rotation: 0, 
                    duration: 0.9, 
                    stagger: {
                        amount: 1.2,
                        from: "start"
                    },
                    ease: "back.out(1.6)"
                }
            );
        }, 100);
    }

    /**
     * Domino LED Light-Up Wave Effect across all "ĐA" pins
     */
    async triggerDominoLedEffect() {
        for (let i = 0; i < this.daPinMarkers.length; i++) {
            const el = document.getElementById(`daPin_${i}`);
            if (el) {
                el.classList.add('led-domino-active');
            }
            await new Promise(r => setTimeout(r, 100)); // Fast sequential LED wave
        }
    }

    /**
     * Compute HUGE "ĐA" (Đông Anh) Map Grid Coordinates for 33 products spanning full district
     */
    generateDaCoordinates(count = 33) {
        const centerLat = 21.135;
        const centerLng = 105.868;
        const latScale = 0.082; // Massive height spanning ~9.1km
        const lngScale = 0.125; // Massive width spanning ~13.8km

        // Exactly 33 distinct, beautifully spaced points forming "Đ" (18 pts) and "A" (15 pts)
        const letterPoints = [
            // --- LETTER Đ (18 points: Index 0 - 17) ---
            // Left Vertical Spine of Đ (5 points)
            { x: -0.65, y: 0.50 },
            { x: -0.65, y: 0.25 },
            { x: -0.65, y: 0.00 },
            { x: -0.65, y: -0.25 },
            { x: -0.65, y: -0.50 },

            // Horizontal Crossbar of Đ (3 points)
            { x: -0.85, y: 0.00 },
            { x: -0.45, y: 0.00 },
            { x: -0.25, y: 0.00 },

            // Top Bar of Đ (3 points)
            { x: -0.50, y: 0.50 },
            { x: -0.35, y: 0.50 },
            { x: -0.20, y: 0.48 },

            // Curved Outer Belly of Đ (4 points)
            { x: -0.10, y: 0.32 },
            { x: -0.05, y: 0.00 },
            { x: -0.10, y: -0.32 },
            { x: -0.20, y: -0.48 },

            // Bottom Bar of Đ (3 points)
            { x: -0.50, y: -0.50 },
            { x: -0.35, y: -0.50 },
            { x: -0.25, y: -0.50 },

            // --- LETTER A (15 points: Index 18 - 32) ---
            // Left Slanted Leg of A (5 points)
            { x: 0.15, y: -0.50 },
            { x: 0.23, y: -0.25 },
            { x: 0.31, y: 0.00 },
            { x: 0.39, y: 0.25 },
            { x: 0.47, y: 0.50 },

            // Right Slanted Leg of A (5 points)
            { x: 0.55, y: 0.25 },
            { x: 0.63, y: 0.00 },
            { x: 0.71, y: -0.25 },
            { x: 0.79, y: -0.50 },
            { x: 0.47, y: 0.50 }, // Apex

            // Middle Horizontal Crossbar of A (5 points)
            { x: 0.23, y: -0.05 },
            { x: 0.31, y: -0.05 },
            { x: 0.39, y: -0.05 },
            { x: 0.47, y: -0.05 },
            { x: 0.55, y: -0.05 }
        ];

        const result = [];
        for (let i = 0; i < count; i++) {
            const pt = letterPoints[i % letterPoints.length];
            result.push({
                lat: centerLat + (pt.y * latScale),
                lng: centerLng + (pt.x * lngScale)
            });
        }

        return result;
    }
}

window.OcopMarkerController = OcopMarkerController;
