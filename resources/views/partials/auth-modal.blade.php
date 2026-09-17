{{-- Global Auth Guard — Food Tour Style Invite Popup Modal --}}
    <div id="authLoginModal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(15, 23, 42, 0.65); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); align-items:center; justify-content:center; padding: 20px; box-sizing: border-box;" onclick="if(event.target===this)closeAuthLoginModal()">
        <div style="background:#ffffff; border-radius:24px; width:92%; max-width:420px; padding: 32px 24px 28px 24px; box-shadow:0 25px 80px rgba(0,0,0,0.3); border: 1.5px solid rgba(14, 165, 233, 0.25); text-align:center; position:relative; animation:authModalIn .3s ease; box-sizing: border-box;">
            <button onclick="closeAuthLoginModal()" style="position:absolute; top:16px; right:16px; width: 32px; height: 32px; border-radius: 50%; background:#f1f5f9; border:none; font-size:1.1rem; color:#64748b; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'" aria-label="Đóng">✕</button>
            
            <div style="width:68px; height:68px; border-radius:50%; background:linear-gradient(135deg, #0ea5e9, #0284c7); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:2.2rem; margin-bottom:16px; box-shadow:0 10px 25px -5px rgba(14,165,233,0.45);">
                🔑
            </div>

            <h3 style="margin:0 0 8px 0; font-size:1.3rem; font-weight:800; color:#0f172a; font-family: var(--font-heading, inherit);" id="authLoginTitle">Đăng nhập tài khoản</h3>
            
            <p id="authLoginMessage" style="margin:0 0 24px 0; font-size:0.9rem; color:#64748b; line-height:1.6; font-weight: 500;">
                Bạn cần đăng nhập hoặc đăng ký tài khoản Đông Anh Discovery để trải nghiệm tính năng này nhé!
            </p>

            <div style="display:flex; gap:12px;">
                <a id="authModalLoginBtn" href="/auth/login" style="flex:1; padding:12px 16px; border-radius:14px; background:linear-gradient(135deg, #0ea5e9, #0284c7); color:#ffffff; font-weight:800; font-size:0.92rem; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:6px; box-shadow:0 8px 20px -4px rgba(14, 165, 233, 0.4); transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <span>🔑</span> Đăng nhập
                </a>
                <a id="authModalRegisterBtn" href="/auth/register" style="flex:1; padding:12px 16px; border-radius:14px; background:#f8fafc; color:#0284c7; font-weight:800; font-size:0.92rem; text-decoration:none; border:1.5px solid #e0f2fe; display:inline-flex; align-items:center; justify-content:center; gap:6px; transition:background 0.2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#f8fafc'">
                    <span>✨</span> Đăng ký
                </a>
            </div>

            <button type="button" onclick="closeAuthLoginModal()" style="background:transparent; border:none; color:#94a3b8; font-size:0.85rem; font-weight:600; cursor:pointer; padding:8px 0 0 0; margin-top:14px; font-family:inherit; width:100%;">
                Để sau
            </button>
        </div>
    </div>
    <style>
        @keyframes authModalIn { from { opacity:0; transform:scale(.93) translateY(12px); } to { opacity:1; transform:scale(1) translateY(0); } }
        @keyframes toastIn { from { opacity:0; transform:translateX(-50%) translateY(-10px); } to { opacity:1; transform:translateX(-50%) translateY(0); } }
    </style>
    <script>
    var __IS_LOGGED_IN = @json(!!(Auth::check() || session('user_id')));

    function checkAuthGuard(actionName) {
        var allowGuestActions = ['thích bài viết', 'bình luận', 'thả tim địa điểm', 'tương tác bài viết', 'thích'];
        if (actionName && allowGuestActions.indexOf(actionName.toLowerCase()) !== -1) {
            return true;
        }
        if (!__IS_LOGGED_IN) {
            openAuthLoginModal(actionName);
            return false;
        }
        return true;
    }

    function openAuthLoginModal(actionName) {
        var modal = document.getElementById('authLoginModal');
        var msgEl = document.getElementById('authLoginMessage');
        var titleEl = document.getElementById('authLoginTitle');
        
        if (actionName) {
            if (titleEl) titleEl.textContent = 'Đăng nhập để ' + actionName;
            if (msgEl) msgEl.textContent = 'Bạn cần đăng nhập hoặc đăng ký tài khoản Đông Anh Discovery để ' + actionName + ' nhé!';
        } else {
            if (titleEl) titleEl.textContent = 'Đăng nhập tài khoản';
            if (msgEl) msgEl.textContent = 'Bạn cần đăng nhập hoặc đăng ký tài khoản Đông Anh Discovery để trải nghiệm tính năng này nhé!';
        }
        if (modal) modal.style.display = 'flex';
    }

    function closeAuthLoginModal() {
        var modal = document.getElementById('authLoginModal');
        if (modal) modal.style.display = 'none';
    }

    function handleAuthLoginSubmit(e) {
        e.preventDefault();
        var email = document.getElementById('authLoginEmail').value.trim();
        var password = document.getElementById('authLoginPassword').value;
        var btn = document.getElementById('authLoginSubmitBtn');
        var errEl = document.getElementById('authLoginError');

        btn.disabled = true;
        btn.textContent = 'Đang đăng nhập...';
        errEl.style.display = 'none';

        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch('/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email, password: password })
        })
        .then(function(res) { return res.json().then(function(data) { return { status: res.status, data: data }; }); })
        .then(function(result) {
            if (result.status >= 200 && result.status < 300 && (result.data.success || result.data.redirect)) {
                // Login success — reload page
                showToastNotification('✅ Đăng nhập thành công!');
                setTimeout(function() { window.location.reload(); }, 600);
            } else {
                errEl.textContent = result.data.message || result.data.error || 'Sai tài khoản hoặc mật khẩu!';
                errEl.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Đăng nhập';
            }
        })
        .catch(function() {
            // Fallback: try form submit redirect
            window.location.href = '/auth/login';
        });
    }

    function showToastNotification(msg) {
        var existing = document.getElementById('globalToastNotif');
        if (existing) existing.remove();
        var toast = document.createElement('div');
        toast.id = 'globalToastNotif';
        toast.textContent = msg;
        toast.style.cssText = 'position:fixed; top:24px; left:50%; transform:translateX(-50%); background:#1e293b; color:#fff; padding:12px 24px; border-radius:12px; font-size:0.9rem; font-weight:600; z-index:9999999; box-shadow:0 8px 30px rgba(0,0,0,0.2); animation:toastIn .3s ease;';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }
    </script>