/**
 * Facebook Feed Post Text Expander ("Xem thêm" / "Ẩn bớt")
 * Automatically detects long post content (> 220 chars or > 4 lines)
 * and appends interactive Xem thêm / Ẩn bớt toggle button.
 */
function initPostTextExpanders(target) {
    const scope = (target && (target instanceof HTMLElement || target instanceof Document)) ? target : document;
    const postTexts = scope.querySelectorAll('.fb-post-text');

    postTexts.forEach(function(postTextEl) {
        if (postTextEl.dataset.expanderInit === 'true') return;

        let bodyEl = postTextEl.querySelector('.fb-post-text-body');
        if (!bodyEl) {
            const titleEl = postTextEl.querySelector('strong');
            bodyEl = document.createElement('div');
            bodyEl.className = 'fb-post-text-body';

            const nodesToMove = [];
            postTextEl.childNodes.forEach(function(node) {
                if (node !== titleEl) {
                    nodesToMove.push(node);
                }
            });
            nodesToMove.forEach(function(node) {
                bodyEl.appendChild(node);
            });
            postTextEl.appendChild(bodyEl);
        }

        // Mark as initialized only after wrapping
        postTextEl.dataset.expanderInit = 'true';

        const rawText = bodyEl.textContent.trim();
        const lineBreaks = (rawText.match(/\n/g) || []).length;

        // If long text (> 200 chars or > 4 lines)
        if (rawText.length > 200 || lineBreaks >= 4) {
            bodyEl.classList.add('collapsed');

            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'fb-post-toggle-btn';
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.innerHTML = '... Xem thêm';

            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const isCollapsed = bodyEl.classList.contains('collapsed');
                if (isCollapsed) {
                    bodyEl.classList.remove('collapsed');
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    toggleBtn.innerHTML = 'Ẩn bớt ▲';
                } else {
                    bodyEl.classList.add('collapsed');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                    toggleBtn.innerHTML = '... Xem thêm ▼';
                    
                    // Smooth scroll back to top of post text if scrolled past
                    const rect = postTextEl.getBoundingClientRect();
                    if (rect.top < 0) {
                        postTextEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }
            });

            postTextEl.appendChild(toggleBtn);
        }
    });
}


async function shareFbPost(postId, postTitle, postImages) {
    const shareUrl = window.location.href;
    const titleText = postTitle ? ('Bài viết: ' + postTitle) : 'Chia sẻ bài viết';
    
    if (navigator.share) {
        const shareData = {
            title: titleText,
            text: postTitle ? (postTitle + ' — DongAnh Social') : 'Xem bài viết này trên DongAnh Social',
            url: shareUrl
        };

        let imagesArray = [];
        if (Array.isArray(postImages)) {
            imagesArray = postImages;
        } else if (typeof postImages === 'string' && postImages.startsWith('[')) {
            try { imagesArray = JSON.parse(postImages); } catch(e) {}
        }

        // If post has images, attach 1st image file so OS share preview shows the post image
        if (imagesArray.length > 0 && imagesArray[0]) {
            try {
                const firstImgUrl = imagesArray[0];
                const res = await fetch(firstImgUrl);
                const blob = await res.blob();
                const file = new File([blob], 'post-image.jpg', { type: blob.type || 'image/jpeg' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    shareData.files = [file];
                }
            } catch (err) {
                console.log('Non-critical share image fetch:', err);
            }
        }

        navigator.share(shareData).catch(() => {});
        return;
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(shareUrl).then(() => {
            if (typeof showToastNotification === 'function') {
                showToastNotification('🔄 Đã sao chép liên kết bài viết vào khay nhớ tạm!');
            } else if (typeof window.showToast === 'function') {
                window.showToast('🔄 Đã sao chép liên kết bài viết!', 'success');
            } else {
                alert('Đã sao chép liên kết bài viết!');
            }
        }).catch(() => {
            fallbackCopyPostUrl(shareUrl);
        });
    } else {
        fallbackCopyPostUrl(shareUrl);
    }
}

function fallbackCopyPostUrl(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        if (typeof showToastNotification === 'function') {
            showToastNotification('🔄 Đã sao chép liên kết bài viết!');
        } else if (typeof window.showToast === 'function') {
            window.showToast('🔄 Đã sao chép liên kết bài viết!', 'success');
        } else {
            alert('Đã sao chép liên kết bài viết!');
        }
    } catch (err) {
        alert('Liên kết bài viết: ' + text);
    }
    document.body.removeChild(textArea);
}

/* Universal Facebook Photo Lightbox Gallery */
window.currentLightboxImages = window.currentLightboxImages || [];
window.currentLightboxIndex = window.currentLightboxIndex || 0;

function ensurePostLightboxModal() {
    let modal = document.getElementById('postLightboxModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'postLightboxModal';
        modal.className = 'modal';
        modal.style.cssText = 'display:none; position:fixed; z-index:99999; left:0; top:0; width:100%; height:100%; overflow:hidden; background-color:rgba(0,0,0,0.92); backdrop-filter:blur(10px); justify-content:center; align-items:center;';
        modal.innerHTML = `
            <span style="position:absolute; top:20px; right:25px; color:#ffffff; font-size:36px; font-weight:bold; cursor:pointer; z-index:100000; text-shadow: 0 2px 8px rgba(0,0,0,0.5);" onclick="closePostLightbox()">&times;</span>
            <button type="button" onclick="navigateLightbox(-1)" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); color:#fff; border:none; border-radius:50%; width:48px; height:48px; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:100000; backdrop-filter:blur(5px);">‹</button>
            <button type="button" onclick="navigateLightbox(1)" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); color:#fff; border:none; border-radius:50%; width:48px; height:48px; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:100000; backdrop-filter:blur(5px);">›</button>
            <div style="position:relative; max-width:90vw; max-height:90vh; display:flex; align-items:center; justify-content:center;">
                <img id="lightboxCurrentImg" style="max-width:90vw; max-height:85vh; border-radius:12px; object-fit:contain; box-shadow:0 12px 40px rgba(0,0,0,0.8);">
                <div id="lightboxCounter" style="position:absolute; bottom:-35px; color:rgba(255,255,255,0.85); font-size:0.85rem; font-weight:700; background:rgba(0,0,0,0.5); padding:4px 12px; border-radius:20px;"></div>
            </div>
        `;
        document.body.appendChild(modal);

        // Click backdrop to close
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closePostLightbox();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (modal.style.display === 'flex') {
                if (e.key === 'Escape') closePostLightbox();
                if (e.key === 'ArrowLeft') navigateLightbox(-1);
                if (e.key === 'ArrowRight') navigateLightbox(1);
            }
        });
    }
    return modal;
}

window.openPostLightboxGallery = function(images, startIndex = 0) {
    if (!images || !images.length) return;
    if (typeof images === 'string') {
        try { images = JSON.parse(images); } catch(e) { images = [images]; }
    }
    window.currentLightboxImages = images;
    window.currentLightboxIndex = startIndex;
    const modal = ensurePostLightboxModal();
    updateLightboxView();
    modal.style.display = 'flex';
};

window.openPostLightbox = function(src) {
    window.openPostLightboxGallery([src], 0);
};

window.updateLightboxView = function() {
    if (!window.currentLightboxImages.length) return;
    const imgEl = document.getElementById('lightboxCurrentImg');
    const counterEl = document.getElementById('lightboxCounter');
    if (imgEl) imgEl.src = window.currentLightboxImages[window.currentLightboxIndex];
    if (counterEl) counterEl.textContent = `${window.currentLightboxIndex + 1} / ${window.currentLightboxImages.length}`;
};

window.navigateLightbox = function(dir) {
    if (!window.currentLightboxImages.length) return;
    const len = window.currentLightboxImages.length;
    window.currentLightboxIndex = (window.currentLightboxIndex + dir + len) % len;
    updateLightboxView();
};

window.closePostLightbox = function() {
    const modal = document.getElementById('postLightboxModal');
    if (modal) modal.style.display = 'none';
};

document.addEventListener('DOMContentLoaded', function() {
    initPostTextExpanders();
});
