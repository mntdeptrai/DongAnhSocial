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

document.addEventListener('DOMContentLoaded', function() {
    initPostTextExpanders();
});
