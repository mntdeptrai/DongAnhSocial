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
                { opacity: 1, y: 0, scale: 1, duration: 0.6, ease: 'power3.out' }
            );
        }

        await new Promise(r => setTimeout(r, 1000));

        // 2. Fade out Intro Overlay cleanly
        return new Promise(resolve => {
            gsap.to(introOverlay, {
                opacity: 0,
                duration: 0.4,
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

        // Run high-speed Memory Flashback (Tua ký ức) with shooting-photo animation
        if (prods && prods.length > 0 && flashbackContainer && flashbackImage && flashbackTitle && flashbackBadge) {
            // Show flashback container with fade-in and scale-in
            flashbackContainer.style.display = 'flex';
            gsap.killTweensOf(flashbackContainer);
            gsap.fromTo(flashbackContainer, 
                { opacity: 0, scale: 0.9 },
                { opacity: 1, scale: 1, duration: 0.4, ease: 'power2.out' }
            );

            // Get target frame viewport rectangle once
            const targetEl = document.querySelector('.flashback-frame');
            const targetRect = targetEl ? targetEl.getBoundingClientRect() : { left: window.innerWidth / 2 - 140, top: window.innerHeight / 2 - 140 };

            // Rapid loop through all products
            for (let i = 0; i < prods.length; i++) {
                const p = prods[i];
                const pinEl = document.getElementById(`daPin_${i}`);

                if (pinEl && targetEl) {
                    const pinRect = pinEl.getBoundingClientRect();

                    // Create temporary flying photo element
                    const flyer = document.createElement('div');
                    flyer.className = 'ocop-flying-photo';
                    flyer.style.position = 'absolute';
                    flyer.style.left = `${pinRect.left}px`;
                    flyer.style.top = `${pinRect.top}px`;
                    flyer.style.width = '34px';
                    flyer.style.height = '34px';
                    flyer.style.borderRadius = '50%';
                    flyer.style.border = '2.5px solid #D4A017';
                    flyer.style.backgroundImage = `url('${p.image}')`;
                    flyer.style.backgroundSize = 'cover';
                    flyer.style.backgroundPosition = 'center';
                    flyer.style.zIndex = '99999';
                    flyer.style.pointerEvents = 'none';
                    flyer.style.boxShadow = '0 0 15px #D4A017';

                    sloganOverlay.appendChild(flyer);

                    // Animate flyer from pin position to central frame asynchronously
                    gsap.to(flyer, {
                        left: targetRect.left,
                        top: targetRect.top,
                        width: 280,
                        height: 280,
                        borderRadius: '20px',
                        opacity: 0.95,
                        duration: 0.38,
                        ease: 'power2.out',
                        onComplete: () => {
                            flashbackImage.src = p.image || '';
                            flashbackTitle.innerText = p.name || '';
                            flashbackBadge.innerText = p.star_rating || 'OCOP 4 SAO';

                            // High-speed projector flash effect
                            gsap.fromTo(flashbackImage,
                                { scale: 0.95, filter: 'brightness(1.5) contrast(1.2)' },
                                { scale: 1, filter: 'brightness(1) contrast(1)', duration: 0.08, ease: 'power1.out' }
                            );

                            flyer.remove();
                        }
                    });
                } else {
                    // Fallback if elements are missing
                    flashbackImage.src = p.image || '';
                    flashbackTitle.innerText = p.name || '';
                    flashbackBadge.innerText = p.star_rating || 'OCOP 4 SAO';
                }

                await new Promise(r => setTimeout(r, 75)); // 75ms spacing between photo launches
            }

            // Wait for the final flyers to land (380ms flight duration + 100ms safety)
            await new Promise(r => setTimeout(r, 480));

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
