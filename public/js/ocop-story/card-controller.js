/**
 * CARD CONTROLLER - OCOP STORYTELLING
 * OCOP Premium White Glassmorphism Card (360x240 Image, Parallax, 1.2s Shine Sweep, Progress Counter 04/25)
 */

class OcopCardController {
    constructor() {
        this.cardContainer = document.getElementById('ocopCinematicCardContainer');
        this.progressCounter = document.getElementById('ocopProgressCounterText');
    }

    /**
     * Update top progress counter (e.g. 04 / 25)
     */
    updateProgress(currentIndex, totalCount) {
        const counterEl = document.getElementById('ocopProgressCounterText');
        if (counterEl) {
            const currentStr = String(currentIndex + 1).padStart(2, '0');
            const totalStr = String(totalCount).padStart(2, '0');
            counterEl.innerHTML = `<span style="color: #F0C24B; font-weight: 900;">${currentStr}</span> / <span style="color: #D6EED8;">${totalStr}</span>`;
        }

        // Update header phase text
        const phaseLabel = document.getElementById('ocopPhaseLabel');
        if (phaseLabel && window.ocopStoryController && window.ocopStoryController.products[currentIndex]) {
            const prod = window.ocopStoryController.products[currentIndex];
            phaseLabel.innerText = `SẢN PHẨM ${String(currentIndex + 1).padStart(2, '0')}/${totalCount}: ${prod.name.toUpperCase()}`;
        }
    }

    /**
     * Slide in Glassmorphism Card from Right Side
     */
    async slideInCard(product, index, totalCount) {
        this.updateProgress(index, totalCount);

        const card = document.getElementById('ocopCinematicGlassCard');
        if (!card) return;

        // Populate card contents
        document.getElementById('ocopCinematicImg').src = product.image;
        document.getElementById('ocopCinematicTitle').innerText = product.name;
        
        const badgeText = product.star_rating ? (product.star_rating.includes('⭐') ? product.star_rating : `⭐ ${product.star_rating}`) : '⭐ OCOP 4 SAO';
        document.getElementById('ocopCinematicBadge').innerText = badgeText;
        
        document.getElementById('ocopCinematicProducer').innerText = `🏢 Đơn vị: ${product.seller_name || product.eatery_name || 'Cơ sở sản xuất Đông Anh'}`;
        const rawAddress = product.address || product.eatery_address || 'Đông Anh, Hà Nội';
        const cleanedAddress = rawAddress.replace(/^[A-Z0-9]{4,8}\+[A-Z0-9]{2,4}(,\s*|\s+)?/i, '').trim();
        document.getElementById('ocopCinematicAddress').innerText = `📍 Địa chỉ: ${cleanedAddress}`;
        document.getElementById('ocopCinematicPrice').innerText = product.price || 'Giá niêm yết';
        document.getElementById('ocopCinematicDesc').innerText = product.description || 'Sản phẩm OCOP đặc trưng đạt tiêu chuẩn chất lượng cao của Xã Đông Anh.';

        if (product.id) {
            document.getElementById('ocopCinematicQr').src = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://donganh.hanoi.gov.vn/san-pham/${product.id}`;
        }

        // Re-trigger 1.2s Image Shine Overlay animation
        const shineEl = card.querySelector('.image-shine-overlay');
        if (shineEl) {
            shineEl.style.animation = 'none';
            shineEl.offsetHeight; // trigger reflow
            shineEl.style.animation = 'shineSweep 1.2s ease-in-out 0.2s 1';
        }

        // Animate Slide In using GSAP
        gsap.killTweensOf(card);
        gsap.fromTo(card, 
            { x: '110%', opacity: 0, scale: 0.95 },
            { x: '0%', opacity: 1, scale: 1, duration: 0.65, ease: 'power3.out' }
        );

        // Animate image parallax & slow zoom
        const img = document.getElementById('ocopCinematicImg');
        if (img) {
            gsap.fromTo(img,
                { scale: 1 },
                { scale: 1.08, duration: 4, ease: 'linear' }
            );
        }
    }

    /**
     * Slide out card back to the right
     */
    async slideOutCard() {
        const card = document.getElementById('ocopCinematicGlassCard');
        if (!card) return;

        return new Promise(resolve => {
            gsap.to(card, {
                x: '110%',
                opacity: 0,
                scale: 0.95,
                duration: 0.45,
                ease: 'power3.in',
                onComplete: resolve
            });
        });
    }
}

window.OcopCardController = OcopCardController;
