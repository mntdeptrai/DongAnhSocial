/**
 * ANIMATION CONTROLLER - OCOP STORYTELLING
 * GSAP Timelines, Intro Countdown 3-2-1, Slogan Chain Overlay & Grand Finale Stats Counter
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
     * Cinematic Intro Overlay: Clean Intro Screen without countdown
     */
    async playCinematicIntro(totalCount = 33) {
        const introOverlay = document.getElementById('ocopCinematicIntroOverlay');
        const countText = document.getElementById('ocopIntroCountText');
        
        if (countText) countText.innerText = `Khám phá ${totalCount} sản phẩm OCOP tiêu biểu`;

        if (!introOverlay) return;

        introOverlay.style.display = 'flex';
        introOverlay.style.opacity = '1';

        // 1. Fade in Logo & Title smoothly
        const titleEl = document.getElementById('ocopIntroMainTitle');
        if (titleEl) {
            gsap.fromTo(titleEl, 
                { opacity: 0, y: 30, scale: 0.9 },
                { opacity: 1, y: 0, scale: 1, duration: 1.0, ease: 'power3.out' }
            );
        }

        await new Promise(r => setTimeout(r, 1800));

        // 2. Fade out Intro Overlay cleanly
        return new Promise(resolve => {
            gsap.to(introOverlay, {
                opacity: 0,
                duration: 0.8,
                ease: 'power2.inOut',
                onComplete: () => {
                    introOverlay.style.display = 'none';
                    resolve();
                }
            });
        });
    }

    async playSloganChainAndFinale(prods) {
        const sloganOverlay = document.getElementById('ocopSloganOverlay');
        const sloganTextEl = document.getElementById('ocopSloganText');
        const flashbackContainer = document.getElementById('ocopFlashbackContainer');
        const flashbackImage = document.getElementById('ocopFlashbackImage');
        const flashbackTitle = document.getElementById('ocopFlashbackTitle');
        const flashbackBadge = document.getElementById('ocopFlashbackBadge');

        if (!sloganOverlay) return;

        sloganOverlay.style.display = 'flex';
        sloganOverlay.style.opacity = '1';

        // Hide slogan text element to bypass the slogan chain completely
        if (sloganTextEl) {
            sloganTextEl.style.display = 'none';
        }

        // Run high-speed Memory Flashback (Tua ký ức) if we have products
        if (prods && prods.length > 0 && flashbackContainer && flashbackImage && flashbackTitle && flashbackBadge) {
            // Show flashback container with fade-in and scale-in
            flashbackContainer.style.display = 'flex';
            gsap.killTweensOf(flashbackContainer);
            gsap.fromTo(flashbackContainer, 
                { opacity: 0, scale: 0.9 },
                { opacity: 1, scale: 1, duration: 0.4, ease: 'power2.out' }
            );

            // Rapid loop through all products
            for (let i = 0; i < prods.length; i++) {
                const p = prods[i];
                flashbackImage.src = p.image || '';
                flashbackTitle.innerText = p.name || '';
                flashbackBadge.innerText = p.star_rating || 'OCOP 4 SAO';

                // High-speed projector flash effect
                gsap.fromTo(flashbackImage,
                    { scale: 0.9, filter: 'brightness(1.5) contrast(1.2)' },
                    { scale: 1, filter: 'brightness(1) contrast(1)', duration: 0.06, ease: 'power1.out' }
                );

                await new Promise(r => setTimeout(r, 65)); // 65ms per image (extremely fast cinematic feel)
            }

            // Fade out flashback container smoothly
            await new Promise(resolve => {
                gsap.to(flashbackContainer, {
                    opacity: 0,
                    scale: 0.8,
                    duration: 0.4,
                    ease: 'power2.inOut',
                    onComplete: () => {
                        flashbackContainer.style.display = 'none';
                        resolve();
                    }
                });
            });
        }

        // Show Grand Finale Stats Badge Overlay Card directly
        const finaleCard = document.getElementById('ocopFinaleStatsCard');
        if (finaleCard) {
            finaleCard.style.display = 'block';
            gsap.fromTo(finaleCard,
                { opacity: 0, scale: 0.8, y: 40 },
                { opacity: 1, scale: 1, y: 0, duration: 0.8, ease: 'back.out(1.4)' }
            );
        }
    }
}

window.OcopAnimationController = OcopAnimationController;
