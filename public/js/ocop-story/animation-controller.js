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

    /**
     * Slogan Chain & Grand Finale Overlay Screen
     */
    async playSloganChainAndFinale() {
        const sloganOverlay = document.getElementById('ocopSloganOverlay');
        const sloganTextEl = document.getElementById('ocopSloganText');

        if (!sloganOverlay || !sloganTextEl) return;

        sloganOverlay.style.display = 'flex';
        sloganOverlay.style.opacity = '1';

        // Display slogans sequentially
        for (let i = 0; i < this.slogans.length; i++) {
            sloganTextEl.innerText = this.slogans[i];
            
            await new Promise(r => {
                gsap.fromTo(sloganTextEl,
                    { opacity: 0, scale: 0.8, y: 20 },
                    { 
                        opacity: 1, scale: 1, y: 0, duration: 0.8, ease: 'power3.out',
                        onComplete: () => {
                            setTimeout(() => {
                                gsap.to(sloganTextEl, { opacity: 0, scale: 1.15, duration: 0.5, onComplete: r });
                            }, 1200);
                        }
                    }
                );
            });
        }

        // Show Grand Finale Stats Badge Overlay Card
        const finaleCard = document.getElementById('ocopFinaleStatsCard');
        if (finaleCard) {
            finaleCard.style.display = 'block';
            gsap.fromTo(finaleCard,
                { opacity: 0, scale: 0.8, y: 40 },
                { opacity: 1, scale: 1, y: 0, duration: 1.0, ease: 'back.out(1.4)' }
            );
        }
    }
}

window.OcopAnimationController = OcopAnimationController;
