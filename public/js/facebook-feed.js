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


function shareFbPost(postId) {
    const shareUrl = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: 'Chia sẻ bài viết',
            text: 'Xem bài viết này trên Đông Anh Social',
            url: shareUrl
        }).catch(() => {});
    } else if (navigator.clipboard && window.isSecureContext) {
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
