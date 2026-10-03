    <!-- ==========================================================
         GLOBAL CUSTOM CONFIRMATION MODAL
         ========================================================== -->
    <style>
        .gcc-modal-overlay {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(8px);
            z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 16px;
        }
        .gcc-modal-box {
            background: #ffffff; border-radius: 24px; padding: 28px; width: 100%; max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); position: relative; font-family: 'Be Vietnam Pro', sans-serif;
        }
        .gcc-close-btn {
            position: absolute; top: 18px; right: 18px; background: #f1f5f9; border: none;
            width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 800; color: #64748b; cursor: pointer; transition: background 0.15s;
        }
        .gcc-close-btn:hover { background: #e2e8f0; color: #0f172a; }
        .gcc-modal-header { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
        .gcc-icon-box {
            width: 48px; height: 48px; border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; flex-shrink: 0;
        }
        .gcc-icon-box.danger { background: #fef2f2; color: #ef4444; }
        .gcc-icon-box.warning { background: #fffbe6; color: #f59e0b; }
        .gcc-icon-box.info { background: #eff6ff; color: #3b82f6; }
        .gcc-modal-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3; }
        .gcc-modal-sub { font-size: 0.8rem; color: #64748b; margin: 2px 0 0 0; }
        .gcc-modal-body { font-size: 0.92rem; color: #334155; line-height: 1.6; margin-bottom: 24px; padding-left: 2px; }
        .gcc-modal-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; }
        .gcc-btn {
            padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; border: none; cursor: pointer;
            transition: all 0.2s;
        }
        .gcc-btn-cancel { background: #f1f5f9; color: #475569; }
        .gcc-btn-cancel:hover { background: #e2e8f0; color: #0f172a; }
        .gcc-btn-confirm.danger { background: #ef4444; color: #ffffff; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3); }
        .gcc-btn-confirm.danger:hover { background: #dc2626; transform: translateY(-1px); }
        .gcc-btn-confirm.primary { background: #2563eb; color: #ffffff; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3); }
        .gcc-btn-confirm.primary:hover { background: #1d4ed8; transform: translateY(-1px); }
    </style>

    <div id="globalCustomConfirmModal" style="display:none; opacity:0; transition: opacity 0.25s ease;" class="gcc-modal-overlay">
        <div id="globalCustomConfirmBox" style="transform: scale(0.92); transition: transform 0.25s ease;" class="gcc-modal-box">
            <button type="button" onclick="closeCustomConfirmModal()" class="gcc-close-btn">✕</button>
            <div class="gcc-modal-header">
                <div id="gccModalIconBox" class="gcc-icon-box danger">
                    <span id="gccModalIcon">🗑️</span>
                </div>
                <div>
                    <h3 id="gccModalTitle" class="gcc-modal-title">Xác nhận thao tác</h3>
                    <p id="gccModalSub" class="gcc-modal-sub">Hành động này yêu cầu sự xác nhận từ bạn.</p>
                </div>
            </div>
            <div id="gccModalMessage" class="gcc-modal-body">
                Bạn có chắc chắn muốn thực hiện thao tác này?
            </div>
            <div class="gcc-modal-footer">
                <button type="button" id="gccCancelBtn" onclick="closeCustomConfirmModal()" class="gcc-btn gcc-btn-cancel">
                    Hủy bỏ
                </button>
                <button type="button" id="gccConfirmBtn" class="gcc-btn gcc-btn-confirm danger">
                    Xác nhận
                </button>
            </div>
        </div>
    </div>



    <script>
        let activeCustomConfirmForm = null;
        let activeCustomConfirmCallback = null;

        window.showCustomConfirm = function(event, form, title = 'Xác nhận xóa', message = 'Bạn có chắc chắn muốn thực hiện thao tác này?', isDanger = true) {
            if (event) event.preventDefault();
            activeCustomConfirmForm = form;
            activeCustomConfirmCallback = null;

            openCustomConfirmModal(title, message, isDanger);
            return false;
        };

        window.showConfirmModal = function({ title = 'Xác nhận thao tác', message = 'Bạn có chắc chắn muốn thực hiện thao tác này?', isDanger = true, onConfirm = null }) {
            activeCustomConfirmForm = null;
            activeCustomConfirmCallback = onConfirm;

            openCustomConfirmModal(title, message, isDanger);
        };

        function openCustomConfirmModal(title, message, isDanger) {
            const modal = document.getElementById('globalCustomConfirmModal');
            const box = document.getElementById('globalCustomConfirmBox');
            const titleEl = document.getElementById('gccModalTitle');
            const msgEl = document.getElementById('gccModalMessage');
            const iconBox = document.getElementById('gccModalIconBox');
            const iconEl = document.getElementById('gccModalIcon');
            const confirmBtn = document.getElementById('gccConfirmBtn');

            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.textContent = message;

            if (isDanger) {
                if (iconBox) iconBox.className = 'gcc-icon-box danger';
                if (iconEl) iconEl.textContent = '🗑️';
                if (confirmBtn) {
                    confirmBtn.className = 'gcc-btn gcc-btn-confirm danger';
                    confirmBtn.textContent = 'Xác nhận xóa';
                }
            } else {
                if (iconBox) iconBox.className = 'gcc-icon-box info';
                if (iconEl) iconEl.textContent = '❓';
                if (confirmBtn) {
                    confirmBtn.className = 'gcc-btn gcc-btn-confirm primary';
                    confirmBtn.textContent = 'Đồng ý';
                }
            }

            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                box.style.transform = 'scale(1)';
            }, 10);
        }

        function closeCustomConfirmModal() {
            const modal = document.getElementById('globalCustomConfirmModal');
            const box = document.getElementById('globalCustomConfirmBox');
            if (modal && box) {
                modal.style.opacity = '0';
                box.style.transform = 'scale(0.92)';
                setTimeout(() => {
                    modal.style.display = 'none';
                    activeCustomConfirmForm = null;
                    activeCustomConfirmCallback = null;
                }, 200);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const confirmBtn = document.getElementById('gccConfirmBtn');
            const modal = document.getElementById('globalCustomConfirmModal');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    if (activeCustomConfirmForm) {
                        const formToSubmit = activeCustomConfirmForm;
                        closeCustomConfirmModal();
                        formToSubmit.submit();
                    } else if (activeCustomConfirmCallback) {
                        const callback = activeCustomConfirmCallback;
                        closeCustomConfirmModal();
                        callback();
                    }
                });
            }
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeCustomConfirmModal();
                });
            }
        });
    </script>
