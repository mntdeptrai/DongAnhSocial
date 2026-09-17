{{-- Modal xem danh sách người đã thích --}}
    <div id="postLikersModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); align-items:center; justify-content:center;" onclick="if(event.target===this)closePostLikersModal()">
        <div style="background:#fff; border-radius:16px; width:90%; max-width:400px; max-height:70vh; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,0.2); animation:likersModalIn .25s ease;">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid #f1f5f9;">
                <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#0f172a;">👍 Người đã thích</h3>
                <button onclick="closePostLikersModal()" style="background:none; border:none; font-size:1.3rem; cursor:pointer; color:#94a3b8; padding:4px; line-height:1;" aria-label="Đóng">&times;</button>
            </div>
            <div id="postLikersContent" style="overflow-y:auto; padding:8px 12px; flex:1;">
                <div style="text-align:center; padding:30px; color:#94a3b8;">Đang tải...</div>
            </div>
        </div>
    </div>
    <style>
        @keyframes likersModalIn { from { opacity:0; transform:scale(.95) translateY(10px); } to { opacity:1; transform:scale(1) translateY(0); } }
    </style>
    <script>
    function showPostLikers(postId, type) {
        type = type || 'post';
        const modal = document.getElementById('postLikersModal');
        const content = document.getElementById('postLikersContent');
        modal.style.display = 'flex';
        content.innerHTML = '<div style="text-align:center; padding:30px; color:#94a3b8;">Đang tải...</div>';

        fetch(`/api/reactions/likers?id=${postId}&type=${type}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.likers || data.likers.length === 0) {
                    content.innerHTML = '<div style="text-align:center; padding:30px; color:#94a3b8;">Chưa có ai thích bài viết này.</div>';
                    return;
                }
                let html = '';
                data.likers.forEach(function(liker) {
                    const initial = liker.name ? liker.name.charAt(0).toUpperCase() : '?';
                    const avatarHtml = liker.avatar
                        ? `<img src="${liker.avatar}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #e2e8f0;" alt="${liker.name}">`
                        : `<div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#0284c7,#0369a1); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.9rem; font-weight:700; border:2px solid #e2e8f0;">${initial}</div>`;
                    html += `<div style="display:flex; align-items:center; gap:12px; padding:10px 8px; border-radius:10px; transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        ${avatarHtml}
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:0.92rem; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${liker.name}</div>
                        </div>
                        <span style="font-size:1.1rem;">${liker.emoji}</span>
                    </div>`;
                });
                content.innerHTML = html;
            })
            .catch(() => {
                content.innerHTML = '<div style="text-align:center; padding:30px; color:#ef4444;">Lỗi tải dữ liệu.</div>';
            });
    }
    function closePostLikersModal() {
        document.getElementById('postLikersModal').style.display = 'none';
    }
    </script>