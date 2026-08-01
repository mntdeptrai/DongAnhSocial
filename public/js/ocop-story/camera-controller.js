/**
 * CAMERA CONTROLLER - OCOP STORYTELLING
 * Leaflet Flycam Animation, Smooth Easing & Animated GPS Polylines
 */

class OcopCameraController {
    constructor(map) {
        this.map = map;
        this.activePolyline = null;
    }

    setMap(map) {
        this.map = map;
    }

    /**
     * Eased Flycam Animation: Zoom Out -> Fly -> Zoom In -> Focus
     */
    async flyToTarget(lat, lng, targetZoom = 16, duration = 3.0) {
        if (!this.map) return;

        return new Promise(resolve => {
            this.map.flyTo([lat, lng], targetZoom, {
                duration: duration,
                easeLinearity: 0.25,
                noMoveStart: true
            });

            setTimeout(() => {
                resolve();
            }, duration * 1000 + 100);
        });
    }

    /**
     * Draw glowing GPS line between previous and current product coordinates
     */
    drawGpsRoute(fromLat, fromLng, toLat, toLng) {
        if (!this.map || !fromLat || !fromLng) return;

        if (this.activePolyline) {
            this.map.removeLayer(this.activePolyline);
        }

        const latlngs = [
            [fromLat, fromLng],
            [toLat, toLng]
        ];

        this.activePolyline = L.polyline(latlngs, {
            color: '#D4A017',
            weight: 4,
            opacity: 0.9,
            dashArray: '8, 8',
            lineCap: 'round'
        }).addTo(this.map);

        // Animate line fading out gently
        let opacity = 0.8;
        const fadeTimer = setInterval(() => {
            opacity -= 0.1;
            if (opacity <= 0) {
                clearInterval(fadeTimer);
                if (this.activePolyline && this.map) {
                    this.map.removeLayer(this.activePolyline);
                    this.activePolyline = null;
                }
            } else if (this.activePolyline) {
                this.activePolyline.setStyle({ opacity: opacity });
            }
        }, 150);
    }
}

window.OcopCameraController = OcopCameraController;
