/**
 * Real-time Notification Engine for Seller Portal (Kênh Chủ Gian Hàng Số)
 * - Auto-polling every 5 seconds for both New Orders and Customer Chat Messages
 * - Web Audio API synthesized chime (Ting Ting chuông báo)
 * - Interactive Glassmorphism Toasts with Direct Action Links
 * - Topbar Bell Dropdown with Unread Counter
 * - Topbar & Sidebar Chat Badges
 * - Browser Tab Title Alert
 */

(function () {
    const POLL_INTERVAL_MS = 5000;
    const API_URL = '/seller/api/orders';
    const CHAT_API_URL = '/seller/api/chat/unread';

    let knownOrderIds = new Set();
    let isFirstPoll = true;
    let unreadCount = 0;
    let recentOrdersList = [];
    let titleBlinkInterval = null;
    const originalDocumentTitle = document.title;

    let lastKnownChatMsgId = 0;
    try {
        const stored = localStorage.getItem('donganh_seller_last_chat_id');
        if (stored) lastKnownChatMsgId = parseInt(stored) || 0;
    } catch (_) {}

    // 1. Web Audio API Chime (Ting Ting chuông báo)
    function playChimeSound() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;

            // Note 1: E5 (659.25 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.35, now);
            gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.8);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.8);

            // Note 2: G#5 (830.61 Hz) - 0.14s later
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(830.61, now + 0.14);
            gain2.gain.setValueAtTime(0.4, now + 0.14);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 1.2);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.14);
            osc2.stop(now + 1.2);

            // Note 3: B5 (987.77 Hz) - 0.28s later
            const osc3 = ctx.createOscillator();
            const gain3 = ctx.createGain();
            osc3.type = 'sine';
            osc3.frequency.setValueAtTime(987.77, now + 0.28);
            gain3.gain.setValueAtTime(0.45, now + 0.28);
            gain3.gain.exponentialRampToValueAtTime(0.0001, now + 1.5);
            osc3.connect(gain3);
            gain3.connect(ctx.destination);
            osc3.start(now + 0.28);
            osc3.stop(now + 1.5);
        } catch (e) {
            console.warn('[Seller Notification] AudioContext unavailable:', e);
        }
    }

    // 2. Format Currency Helper
    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount || 0) + 'đ';
    }

    // 3. Tab Title Alert
    function triggerTitleBlink(text) {
        if (titleBlinkInterval) clearInterval(titleBlinkInterval);
        let show = false;
        titleBlinkInterval = setInterval(() => {
            document.title = show ? `🔔 ${text} — DongAnh` : originalDocumentTitle;
            show = !show;
        }, 900);

        setTimeout(() => {
            if (titleBlinkInterval) {
                clearInterval(titleBlinkInterval);
                document.title = originalDocumentTitle;
            }
        }, 15000);
    }

    // 4. Interactive Floating Toast for New Order
    function showNewOrderToast(order) {
        const container = document.getElementById('universal-toast-container');
        if (!container) return;

        const itemsSummary = (order.items && order.items.length > 0)
            ? order.items.map(i => `${i.name} (x${i.quantity})`).join(', ')
            : 'Sản phẩm tại gian hàng';

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: linear-gradient(135deg, rgba(28, 16, 7, 0.97), rgba(45, 26, 11, 0.98));
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 2px solid #d97706;
            color: #ffffff;
            padding: 16px 20px;
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(217, 119, 6, 0.35);
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: auto;
            transform: translateX(120%);
            opacity: 0;
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-family: var(--slr-font, 'Plus Jakarta Sans', sans-serif);
            position: relative;
            overflow: hidden;
        `;

        const orderCodeFormatted = '#ORD-' + String(order.id).padStart(5, '0');

        toast.innerHTML = `
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #f59e0b, #ef4444, #10b981);"></div>

            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #d97706, #b45309); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4);">
                        🎉
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 0.92rem; color: #fbbf24; display: flex; align-items: center; gap: 6px;">
                            CÓ ĐƠN HÀNG MỚI!
                            <span style="font-size: 0.72rem; background: #ef4444; color: white; padding: 1px 6px; border-radius: 6px; font-weight: 900;">MỚI</span>
                        </div>
                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.7); margin-top: 1px;">
                            ${orderCodeFormatted} • ${order.created_at_formatted || 'Vừa xong'}
                        </div>
                    </div>
                </div>
                <button type="button" style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 26px; height: 26px; border-radius: 50%; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;" onclick="this.closest('div').parentElement.remove()">&times;</button>
            </div>

            <div style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 10px 14px; border: 1px solid rgba(255,255,255,0.08); font-size: 0.84rem; line-height: 1.45;">
                <div style="color: #ffffff; font-weight: 700; display: flex; justify-content: space-between;">
                    <span>👤 ${order.customer_name || 'Khách đặt qua App'}</span>
                    <span style="color: #34d399; font-weight: 800;">${formatMoney(order.total_amount)}</span>
                </div>
                <div style="color: rgba(255,255,255,0.8); font-size: 0.78rem; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    📦 ${itemsSummary}
                </div>
                ${order.shipping_address ? `
                    <div style="color: #fde68a; font-size: 0.75rem; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                        <span>📍</span> <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${order.shipping_address.replace('[Ghé sạp lấy đồ]', '').trim()}</span>
                    </div>
                ` : ''}
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 2px;">
                <a href="/seller/orders/${order.id}" style="background: linear-gradient(135deg, #d97706, #b45309); color: #ffffff; font-weight: 800; font-size: 0.8rem; padding: 8px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='none'">
                    👁️ Xem đơn & Chuẩn bị đồ ➔
                </a>
            </div>
        `;

        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });

        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.style.transform = 'translateX(120%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 350);
            }
        }, 12000);
    }

    // 5. Interactive Toast for Customer Chat Message
    function showCustomerChatToast(msg) {
        // Don't show toast if already on /seller/chat
        if (window.location.pathname.includes('/seller/chat')) return;

        const container = document.getElementById('universal-toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.97), rgba(30, 41, 59, 0.98));
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 2px solid #38bdf8;
            color: #ffffff;
            padding: 16px 20px;
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(56, 189, 248, 0.35);
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: auto;
            transform: translateX(120%);
            opacity: 0;
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-family: var(--slr-font, 'Plus Jakarta Sans', sans-serif);
            position: relative;
            overflow: hidden;
        `;

        toast.innerHTML = `
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #38bdf8, #818cf8, #34d399);"></div>

            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #0284c7, #0369a1); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);">
                        💬
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 0.92rem; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                            KHÁCH HÀNG NHẮN TIN!
                            <span style="font-size: 0.72rem; background: #0284c7; color: white; padding: 1px 6px; border-radius: 6px; font-weight: 900;">TIN MỚI</span>
                        </div>
                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.7); margin-top: 1px;">
                            ${msg.sender_name} • ${msg.created_at_formatted || 'Vừa xong'}
                        </div>
                    </div>
                </div>
                <button type="button" style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 26px; height: 26px; border-radius: 50%; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;" onclick="this.closest('div').parentElement.remove()">&times;</button>
            </div>

            <div style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 10px 14px; border: 1px solid rgba(255,255,255,0.08); font-size: 0.86rem; line-height: 1.45; color: #f1f5f9;">
                <span style="font-weight: 700; color: #38bdf8;">"${msg.message_text}"</span>
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 2px;">
                <a href="/seller/chat?customer_id=${msg.user_id}" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #ffffff; font-weight: 800; font-size: 0.82rem; padding: 8px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='none'">
                    💬 Trả lời khách ngay ➔
                </a>
            </div>
        `;

        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });

        setTimeout(() => {
            if (toast && toast.parentElement) {
                toast.style.transform = 'translateX(120%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 350);
            }
        }, 12000);
    }

    // 6. Update Topbar Bell Dropdown List & Badge Counter
    function updateBellUI() {
        const badge = document.getElementById('slr-notif-badge');
        const countText = document.getElementById('slr-notif-count-text');
        const list = document.getElementById('slr-notif-list');

        if (badge) {
            if (unreadCount > 0) {
                badge.style.display = 'inline-flex';
                badge.innerText = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.animation = 'pulse-badge 1.5s infinite';
            } else {
                badge.style.display = 'none';
            }
        }

        if (countText) {
            countText.innerText = `${unreadCount} mới`;
        }

        if (list && recentOrdersList.length > 0) {
            let html = '';
            recentOrdersList.slice(0, 6).forEach(order => {
                const orderCodeFormatted = '#ORD-' + String(order.id).padStart(5, '0');
                const isPendingOrConfirmed = order.status === 'confirmed' || order.status === 'pending';
                
                html += `
                    <a href="/seller/orders/${order.id}" style="display: block; padding: 12px 16px; border-bottom: 1px solid #f3f4f6; text-decoration: none; color: inherit; transition: background 0.2s; background: ${isPendingOrConfirmed ? '#fffbeb' : '#ffffff'};" onmouseover="this.style.background='#fef3c7'" onmouseout="this.style.background='${isPendingOrConfirmed ? '#fffbeb' : '#ffffff'}'">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <strong style="color: #d97706; font-size: 0.86rem;">${orderCodeFormatted}</strong>
                            <span style="font-size: 0.72rem; color: #6b7280;">${order.created_at_formatted || ''}</span>
                        </div>
                        <div style="font-size: 0.82rem; font-weight: 700; color: #1f2937; display: flex; justify-content: space-between; align-items: center;">
                            <span>${order.customer_name || 'Khách đặt sạp'}</span>
                            <span style="color: #059669;">${formatMoney(order.total_amount)}</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${order.shipping_address ? order.shipping_address.replace('[Ghé sạp lấy đồ]', '').trim() : 'Ghé lấy tại sạp'}
                        </div>
                    </a>
                `;
            });
            list.innerHTML = html;
        }
    }

    // 7. Polling Logic for Orders
    async function pollSellerOrders() {
        try {
            const res = await fetch(API_URL, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (!data || !data.orders) return;

            recentOrdersList = data.orders;

            if (isFirstPoll) {
                data.orders.forEach(o => knownOrderIds.add(o.id));
                isFirstPoll = false;
                updateBellUI();
                return;
            }

            let newOrders = [];
            data.orders.forEach(order => {
                if (!knownOrderIds.has(order.id)) {
                    knownOrderIds.add(order.id);
                    newOrders.push(order);
                }
            });

            if (newOrders.length > 0) {
                unreadCount += newOrders.length;
                playChimeSound();
                triggerTitleBlink(`(${newOrders.length}) ĐƠN HÀNG MỚI!`);

                newOrders.forEach(order => {
                    showNewOrderToast(order);
                });

                updateBellUI();
            }
        } catch (err) {}
    }

    // 8. Polling Logic for Customer Chat Messages
    async function pollSellerChat() {
        try {
            const res = await fetch(CHAT_API_URL, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (data.success && data.latest_message) {
                const msg = data.latest_message;
                if (lastKnownChatMsgId === 0) {
                    lastKnownChatMsgId = msg.id;
                    try { localStorage.setItem('donganh_seller_last_chat_id', msg.id); } catch (_) {}
                    return;
                }

                if (msg.id > lastKnownChatMsgId) {
                    lastKnownChatMsgId = msg.id;
                    try { localStorage.setItem('donganh_seller_last_chat_id', msg.id); } catch (_) {}

                    playChimeSound();
                    triggerTitleBlink('Có tin nhắn mới từ khách!');
                    showCustomerChatToast(msg);

                    const topbarChatBadge = document.getElementById('slr-chat-topbar-badge');
                    const sidebarChatBadge = document.getElementById('slr-chat-sidebar-badge');
                    if (topbarChatBadge) {
                        topbarChatBadge.style.display = 'inline-flex';
                        topbarChatBadge.innerText = '1';
                        topbarChatBadge.style.animation = 'pulse-badge 1.5s infinite';
                    }
                    if (sidebarChatBadge) {
                        sidebarChatBadge.style.display = 'inline-block';
                        sidebarChatBadge.innerText = '1';
                    }
                }
            }
        } catch (err) {}
    }

    // 9. Bell Dropdown Toggle Helper
    window.toggleSellerNotifDropdown = function (e) {
        if (e) e.stopPropagation();
        const dropdown = document.getElementById('slr-notif-dropdown');
        if (!dropdown) return;

        const isVisible = dropdown.style.display === 'block';
        dropdown.style.display = isVisible ? 'none' : 'block';

        if (!isVisible) {
            unreadCount = 0;
            updateBellUI();
            if (titleBlinkInterval) {
                clearInterval(titleBlinkInterval);
                document.title = originalDocumentTitle;
            }
        }
    };

    // Close dropdown on click outside
    document.addEventListener('click', function (e) {
        const dropdown = document.getElementById('slr-notif-dropdown');
        const bellBtn = document.getElementById('slr-bell-btn');
        if (dropdown && dropdown.style.display === 'block') {
            if (!dropdown.contains(e.target) && !bellBtn.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        }
    });

    // 10. Start Background Polling
    function init() {
        pollSellerOrders();
        pollSellerChat();
        setInterval(pollSellerOrders, POLL_INTERVAL_MS);
        setInterval(pollSellerChat, POLL_INTERVAL_MS);
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }

})();
