<!-- ==========================================================
         DONGANH SOCIAL IN-APP SHARE MODAL
         ========================================================== -->
    <!-- ==========================================================
         DONGANH SOCIAL IN-APP SHARE MODAL (LIGHT & FRESH THEME)
         ========================================================== -->
    <style>
        .notif-icon-circle {
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            min-height: 42px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.15rem !important;
            flex-shrink: 0 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
            margin: 0 !important;
        }
        .notif-type-reaction { background: #eff6ff !important; color: #2563eb !important; }
        .notif-type-comment { background: #e0f2fe !important; color: #0284c7 !important; }
        .notif-type-reply { background: #ecfdf5 !important; color: #10b981 !important; }
        .notif-type-message { background: #e0e7ff !important; color: #4338ca !important; }
        .notif-type-share { background: #f3e8ff !important; color: #7e22ce !important; }
        .notif-type-friend { background: #fef3c7 !important; color: #d97706 !important; }
        .notif-type-review { background: #fef9c3 !important; color: #ca8a04 !important; }
        .notif-type-default { background: #f1f5f9 !important; color: #475569 !important; }

        @keyframes notifHighlightPulse {
            0% {
                box-shadow: 0 0 0 3.5px #0ea5e9, 0 10px 35px rgba(14, 165, 233, 0.4) !important;
                transform: scale(1.008);
            }
            50% {
                box-shadow: 0 0 0 4px #0284c7, 0 12px 40px rgba(2, 132, 199, 0.5) !important;
                transform: scale(1.012);
            }
            100% {
                box-shadow: none !important;
                transform: scale(1);
            }
        }
        .notif-target-highlight {
            animation: notifHighlightPulse 3.5s ease-in-out forwards !important;
            transition: all 0.3s ease !important;
        }
    </style>

    <script>
    function handleNotifItemClick(url, event) {
        if (!url) return;
        try {
            const targetUrlObj = new URL(url, window.location.origin);
            const currentPath = window.location.pathname;
            const targetPath = targetUrlObj.pathname;
            
            if (currentPath === targetPath) {
                if (event) event.preventDefault();
                window.history.pushState({}, '', url);
                if (typeof window.scrollToNotifTarget === 'function') {
                    window.scrollToNotifTarget();
                } else {
                    window.location.href = url;
                }
            } else {
                window.location.href = url;
            }
        } catch(e) {
            window.location.href = url;
        }
    }
    </script>

    <style>
        .dash-share-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(8px);
            z-index: 999999; display: flex; align-items: flex-end; justify-content: center; padding: 16px;
        }
        @media (min-width: 640px) {
            .dash-share-overlay { align-items: center; }
        }
        .dash-share-box {
            background: #ffffff; color: #0f172a; border-radius: 24px; padding: 24px 20px; width: 92%; max-width: 440px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25); position: relative; font-family: 'Be Vietnam Pro', sans-serif;
            border: 1px solid rgba(0, 0, 0, 0.08); overflow: hidden; box-sizing: border-box;
        }
        .dash-share-close {
            position: absolute; top: 16px; right: 16px; background: #f1f5f9; border: none;
            width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 800; color: #64748b; cursor: pointer; transition: all 0.15s;
        }
        .dash-share-close:hover { background: #e2e8f0; color: #0f172a; }
        .dash-share-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 20px; text-align: center; }
        .dash-share-subtitle { font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; }
        
        .dash-friends-scroll {
            display: flex; gap: 14px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 16px;
            scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; -webkit-overflow-scrolling: touch;
        }
        .dash-friend-item {
            display: flex; flex-direction: column; align-items: center; gap: 6px; width: 72px; flex-shrink: 0;
            cursor: pointer; background: transparent; border: none; padding: 0; color: inherit;
        }
        .dash-friend-avatar-wrap {
            position: relative; width: 56px; height: 56px; border-radius: 50%; background: #f1f5f9;
            display: flex; align-items: center; justify-content: center; transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }
        .dash-friend-item:hover .dash-friend-avatar-wrap {
            transform: scale(1.08); box-shadow: 0 6px 16px rgba(37, 99, 235, 0.25);
        }
        .dash-friend-avatar-wrap img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .dash-friend-avatar-wrap span { font-size: 1.6rem; }
        .dash-friend-online {
            position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; border-radius: 50%;
            background: #10b981; border: 2.5px solid #ffffff;
        }
        .dash-friend-name {
            font-size: 0.76rem; font-weight: 600; color: #334155; text-align: center;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;
        }

        .dash-share-grid {
            display: flex; gap: 10px; overflow-x: auto; padding: 4px 0 10px 0;
            scrollbar-width: none; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box;
        }
        .dash-share-grid::-webkit-scrollbar { display: none; }
        .dash-grid-btn {
            display: flex; flex-direction: column; align-items: center; gap: 6px; background: transparent; border: none;
            color: #334155; cursor: pointer; padding: 6px 2px; border-radius: 16px; transition: all 0.15s;
            width: 64px; flex-shrink: 0; box-sizing: border-box;
        }
        .dash-grid-btn:hover { background: #f8fafc; color: #0f172a; }
        .dash-grid-icon-box {
            width: 50px; height: 50px; border-radius: 16px; background: #f1f5f9; display: flex;
            align-items: center; justify-content: center; font-size: 1.3rem; transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04); flex-shrink: 0;
        }
        .dash-grid-btn:hover .dash-grid-icon-box { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37, 99, 235, 0.2); }
        .dash-grid-btn.zalo .dash-grid-icon-box { background: #0068ff; color: #fff; font-weight: 800; font-size: 0.8rem; }
        .dash-grid-btn.facebook .dash-grid-icon-box { background: #1877f2; color: #fff; font-weight: 800; font-size: 0.85rem; }
        .dash-grid-btn.feed .dash-grid-icon-box { background: #eff6ff; color: #2563eb; }
        .dash-grid-btn.link .dash-grid-icon-box { background: #ecfdf5; color: #10b981; }
        .dash-grid-btn.external .dash-grid-icon-box { background: #fef3c7; color: #d97706; }
        .dash-grid-btn.chat .dash-grid-icon-box { background: #f0f9ff; color: #0284c7; }
        .dash-grid-label {
            font-size: 0.74rem; font-weight: 700; text-align: center; line-height: 1.2; color: #1e293b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;
        }
    </style>

    <div id="dongAnhShareModal" style="display:none; opacity:0; transition: opacity 0.25s ease;" class="dash-share-overlay">
        <div id="dongAnhShareBox" style="transform: translateY(20px); transition: transform 0.25s ease;" class="dash-share-box">
            <button type="button" onclick="closeDongAnhShareModal()" class="dash-share-close">✕</button>
            <div class="dash-share-title">Gửi hoặc Chia sẻ bài viết</div>

            <!-- Section 1: Send via Direct Chat -->
            <div class="dash-share-subtitle">Gửi trực tiếp cho bạn bè</div>
            <div id="dashShareFriendsList" class="dash-friends-scroll">
                <div style="font-size: 0.84rem; color: #64748b; padding: 10px 0;">Đang tải danh sách bạn bè...</div>
            </div>

            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 4px 0 16px 0;">

            <!-- Section 2: Share options -->
            <div class="dash-share-subtitle">Chia sẻ lên hệ sinh thái</div>
            <div class="dash-share-grid">
                <button type="button" class="dash-grid-btn zalo" onclick="shareToZaloWeb()">
                    <div class="dash-grid-icon-box">Zalo</div>
                    <span class="dash-grid-label">Zalo</span>
                </button>
                <button type="button" class="dash-grid-btn facebook" onclick="shareToFacebookWeb()">
                    <div class="dash-grid-icon-box">FB</div>
                    <span class="dash-grid-label">Facebook</span>
                </button>
                <button type="button" class="dash-grid-btn feed" onclick="shareToInternalFeed()">
                    <div class="dash-grid-icon-box">📰</div>
                    <span class="dash-grid-label">Bảng tin</span>
                </button>
                <button type="button" class="dash-grid-btn link" onclick="copySharePostLink()">
                    <div class="dash-grid-icon-box">🔗</div>
                    <span class="dash-grid-label">Sao chép</span>
                </button>
                <button type="button" class="dash-grid-btn external" onclick="triggerExternalShare()">
                    <div class="dash-grid-icon-box">🌐</div>
                    <span class="dash-grid-label">Khác</span>
                </button>
                <button type="button" class="dash-grid-btn chat" onclick="openDirectMessageList()">
                    <div class="dash-grid-icon-box">💬</div>
                    <span class="dash-grid-label">Chat</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentSharingPost = { id: null, hashid: null, title: '', images: [] };

        window.shareFbPost = function(postId, postTitle, postImages, hashid) {
            currentSharingPost = {
                id: postId,
                hashid: hashid || postId,
                title: postTitle || 'Bài viết Đông Anh Social',
                images: Array.isArray(postImages) ? postImages : []
            };

            registerPostShare(postId, hashid || postId);
            openDongAnhShareModal();
        };

        function registerPostShare(postId, hashid) {
            var targetId = hashid || postId;
            if (!targetId) return;

            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('/api/posts/increment-share', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id: targetId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.shares_count !== undefined) {
                    if (postId) {
                        var el = document.getElementById('post-shares-count-' + postId);
                        if (el) el.textContent = data.shares_count;
                    }
                }
            })
            .catch(err => console.log('Share increment error:', err));
        }

        function openDongAnhShareModal() {
            const modal = document.getElementById('dongAnhShareModal');
            const box = document.getElementById('dongAnhShareBox');
            
            if (modal && box) {
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.style.opacity = '1';
                    box.style.transform = 'translateY(0)';
                }, 10);

                loadShareFriendsList();
            }
        }

        function closeDongAnhShareModal() {
            const modal = document.getElementById('dongAnhShareModal');
            const box = document.getElementById('dongAnhShareBox');
            if (modal && box) {
                modal.style.opacity = '0';
                box.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 200);
            }
        }

        function loadShareFriendsList() {
            const container = document.getElementById('dashShareFriendsList');
            if (!container) return;

            fetch('/social/recent-chats', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(friends => {
                if (!Array.isArray(friends) || friends.length === 0) {
                    container.innerHTML = `<div style="font-size: 0.82rem; color: #94a3b8; padding: 10px;">Chưa có bạn bè trong danh sách trò chuyện.</div>`;
                    return;
                }

                container.innerHTML = friends.map(f => `
                    <button type="button" class="dash-friend-item" onclick="sendPostToFriend(${f.id}, '${escapeHtml(f.name)}')">
                        <div class="dash-friend-avatar-wrap">
                            ${f.avatar_url ? `<img onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80';" src="${f.avatar_url}" alt="avatar">` : `<span>${f.avatar || '👤'}</span>`}
                            ${f.is_online ? `<span class="dash-friend-online"></span>` : ''}
                        </div>
                        <span class="dash-friend-name">${escapeHtml(f.name)}</span>
                    </button>
                `).join('');
            })
            .catch(() => {
                container.innerHTML = `<div style="font-size: 0.82rem; color: #94a3b8; padding: 10px;">Chưa kết nối danh sách bạn bè.</div>`;
            });
        }

        function escapeHtml(str) {
            return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function getPostShareUrl() {
            if (currentSharingPost) {
                const shareId = currentSharingPost.hashid || currentSharingPost.id;
                if (shareId) {
                    return window.location.origin + '/ban-tin?post=' + shareId;
                }
            }
            return window.location.href;
        }

        function sendPostToFriend(friendId, friendName) {
            const shareUrl = getPostShareUrl();
            const postTitle = (currentSharingPost && currentSharingPost.title) ? currentSharingPost.title : 'Bài viết';
            const msg = `📰 [Chia sẻ bài viết] ${postTitle}\n🔗 Xem tại: ${shareUrl}`;
            
            closeDongAnhShareModal();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            fetch('/social/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    receiver_id: friendId,
                    message: msg
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' || data.success) {
                    if (typeof showToastNotification === 'function') {
                        showToastNotification(`💬 Đã gửi bài viết cho ${friendName}!`);
                    } else if (typeof window.showToast === 'function') {
                        window.showToast(`💬 Đã gửi bài viết cho ${friendName}!`, 'success');
                    }
                    if (window.Alpine && Alpine.store('chatStore')) {
                        const chatStore = Alpine.store('chatStore');
                        const chat = chatStore.openChats.find(c => c.id === friendId);
                        if (chat && data.message) {
                            chat.messages.push(data.message);
                            chatStore.scrollToBottom(friendId);
                        }
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message || 'Không thể gửi bài viết', 'error');
                    }
                }
            })
            .catch(err => {
                console.error('Lỗi chia sẻ tin nhắn:', err);
            });
        }

        function copySharePostLink() {
            closeDongAnhShareModal();
            const shareUrl = getPostShareUrl();
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(shareUrl).then(() => {
                    if (typeof showToastNotification === 'function') {
                        showToastNotification('🔗 Đã sao chép liên kết trực tiếp bài viết!');
                    } else if (typeof window.showToast === 'function') {
                        window.showToast('🔗 Đã sao chép liên kết trực tiếp bài viết!', 'success');
                    }
                }).catch(() => fallbackCopyPostLink(shareUrl));
            } else {
                fallbackCopyPostLink(shareUrl);
            }
        }

        function fallbackCopyPostLink(text) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                if (typeof showToastNotification === 'function') {
                    showToastNotification('🔗 Đã sao chép liên kết trực tiếp bài viết!');
                } else if (typeof window.showToast === 'function') {
                    window.showToast('🔗 Đã sao chép liên kết trực tiếp bài viết!', 'success');
                }
            } catch (err) {}
            document.body.removeChild(ta);
        }

        function shareToInternalFeed() {
            closeDongAnhShareModal();
            if (typeof showToastNotification === 'function') {
                showToastNotification('📰 Đã chia sẻ lại bài viết lên trang cá nhân!');
            } else if (typeof window.showToast === 'function') {
                window.showToast('📰 Đã chia sẻ lại bài viết lên trang cá nhân!', 'success');
            }
        }

        function openDirectMessageList() {
            closeDongAnhShareModal();
            window.location.href = '/social';
        }

        async function triggerExternalShare() {
            closeDongAnhShareModal();
            const shareUrl = getPostShareUrl();
            const shareData = {
                title: currentSharingPost.title,
                text: currentSharingPost.title + ' — DongAnh Social',
                url: shareUrl
            };

            if (currentSharingPost.images && currentSharingPost.images.length > 0) {
                try {
                    const firstImgUrl = currentSharingPost.images[0];
                    const res = await fetch(firstImgUrl);
                    const blob = await res.blob();
                    const file = new File([blob], 'post-image.jpg', { type: blob.type || 'image/jpeg' });
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        shareData.files = [file];
                    }
                } catch (e) {}
            }

            if (navigator.share) {
                navigator.share(shareData).catch(() => {});
            }
        }

        function shareToFacebookWeb() {
            closeDongAnhShareModal();
            const shareUrl = getPostShareUrl();
            if (currentSharingPost && currentSharingPost.id) {
                registerPostShare(currentSharingPost.id, currentSharingPost.hashid);
            }
            const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
            window.open(fbUrl, 'share-facebook', 'width=600,height=500,location=no,menubar=no,toolbar=no');
        }

        function shareToZaloWeb() {
            closeDongAnhShareModal();
            const shareUrl = getPostShareUrl();
            if (currentSharingPost && currentSharingPost.id) {
                registerPostShare(currentSharingPost.id, currentSharingPost.hashid);
            }
            const zaloUrl = `https://sp.zalo.me/share_inline?link=${encodeURIComponent(shareUrl)}`;
            window.open(zaloUrl, 'share-zalo', 'width=600,height=500,location=no,menubar=no,toolbar=no');
        }
    </script>