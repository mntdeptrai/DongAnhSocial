/**
 * Real-time Customer Notification Engine
 * - Polls /api/orders every 3.5 seconds
 * - Web Audio API dual-tone chime
 * - Glassmorphic Toast Card with animations & actions
 * - Instant page synchronization for Checkout Success & Orders pages
 * - Tab Title Alert
 */

(function () {
    const POLL_INTERVAL_MS = 3500;
    const API_URL = '/api/orders?status=all';
    const STORAGE_KEY = 'donganh_cust_order_statuses';

    let knownOrderStatusMap = {};
    try {
        const cached = localStorage.getItem(STORAGE_KEY);
        if (cached) {
            knownOrderStatusMap = JSON.parse(cached) || {};
        }
    } catch (_) {}

    let isFirstPoll = Object.keys(knownOrderStatusMap).length === 0;
    let titleBlinkInterval = null;
    const originalDocumentTitle = document.title;

    // 1. Web Audio API Chime (Ting-Ting chuông báo)
    function playCustomerChime() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;

            // Note 1: F#5 (739.99 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(739.99, now);
            gain1.gain.setValueAtTime(0.35, now);
            gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.8);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.8);

            // Note 2: A#5 (932.33 Hz) - 0.14s later
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(932.33, now + 0.14);
            gain2.gain.setValueAtTime(0.4, now + 0.14);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 1.2);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.14);
            osc2.stop(now + 1.2);

            // Note 3: C#6 (1108.73 Hz) - 0.28s later
            const osc3 = ctx.createOscillator();
            const gain3 = ctx.createGain();
            osc3.type = 'sine';
            osc3.frequency.setValueAtTime(1108.73, now + 0.28);
            gain3.gain.setValueAtTime(0.45, now + 0.28);
            gain3.gain.exponentialRampToValueAtTime(0.0001, now + 1.5);
            osc3.connect(gain3);
            gain3.connect(ctx.destination);
            osc3.start(now + 0.28);
            osc3.stop(now + 1.5);
        } catch (e) {
            console.warn('[Customer Notification] Audio error:', e);
        }
    }

    // 2. Tab Title Alert
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

    // 3. Ensure Floating Toast Container Exists
    function getToastContainer() {
        let container = document.getElementById('customer-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'customer-toast-container';
            container.style.cssText = `
                position: fixed;
                top: 24px;
                right: 24px;
                z-index: 2147483647;
                display: flex;
                flex-direction: column;
                gap: 14px;
                max-width: 450px;
                width: calc(100% - 48px);
                pointer-events: none;
                font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, sans-serif;
            `;
            document.body.appendChild(container);
        }
        return container;
    }

    // 4. Interactive Glassmorphic Toast Card
    function showCustomerOrderToast(order, oldStatus, newStatus) {
        const container = getToastContainer();
        const orderCode = order.order_code_full || ('#ORD' + String(order.id).padStart(6, '0'));
        const pickupCode = 'MCP-' + String(order.id).padStart(5, '0');
        const stallName = order.eatery_name || order.stall_name || 'Gian hàng';
        const cleanCode = orderCode.replace('#', '');

        let config = {
            icon: '📦',
            title: 'CẬP NHẬT TIẾN TRÌNH ĐƠN HÀNG!',
            body: `Đơn hàng <strong>${orderCode}</strong> tại <strong>${stallName}</strong> đã được cập nhật trạng thái mới.`,
            themeColor: '#059669',
            borderColor: '#10b981',
            actionText: 'Xem chi tiết đơn ➔',
            actionUrl: `/orders/${cleanCode}`
        };

        const st = String(newStatus).toLowerCase();

        if (st === 'ready' || st.includes('sẵn sàng') || st.includes('chờ lấy')) {
            config = {
                icon: '🏪',
                title: 'ĐỒ ĐÃ SẴN SÀNG TẠI SẠP!',
                body: `Chủ gian <strong>${stallName}</strong> đã chuẩn bị & đóng gói xong túi đồ cho bạn. Mời bạn ghé sạp nhận đồ nhé!`,
                themeColor: '#0284c7',
                borderColor: '#38bdf8',
                badgeCode: pickupCode,
                actionText: 'Xem mã nhận đồ ➔',
                actionUrl: `/orders/${cleanCode}`
            };
            triggerTitleBlink('Đồ đã sẵn sàng tại sạp!');
        } else if (st === 'completed' || st.includes('hoàn thành')) {
            config = {
                icon: '🎉',
                title: 'ĐƠN HÀNG ĐÃ HOÀN THÀNH!',
                body: `Cảm ơn bạn đã mua hàng tại <strong>${stallName}</strong>. Hãy chia sẻ cảm nhận và đánh giá món ăn nhé!`,
                themeColor: '#059669',
                borderColor: '#34d399',
                actionText: '⭐ Đánh giá trải nghiệm ➔',
                actionUrl: `/orders/${cleanCode}`
            };
            triggerTitleBlink('Đơn hàng đã hoàn thành!');
        } else if (st === 'shipping' || st === 'delivering' || st.includes('vận chuyển')) {
            config = {
                icon: '🚚',
                title: 'ĐƠN HÀNG ĐANG ĐƯỢC GIAO!',
                body: `Tài xế đang vận chuyển đơn hàng ${orderCode} tới địa chỉ của bạn.`,
                themeColor: '#2563eb',
                borderColor: '#60a5fa',
                actionText: 'Theo dõi hành trình ➔',
                actionUrl: `/orders/${cleanCode}`
            };
            triggerTitleBlink('Đơn hàng đang được giao!');
        } else if (st === 'confirmed' || st === 'preparing' || st.includes('chuẩn bị')) {
            config = {
                icon: '👨‍🍳',
                title: 'SẠP ĐÃ NHẬN ĐƠN!',
                body: `Chủ sạp <strong>${stallName}</strong> đã tiếp nhận và đang tiến hành chuẩn bị nguyên liệu cho đơn <strong>${orderCode}</strong>.`,
                themeColor: '#d97706',
                borderColor: '#f59e0b',
                actionText: 'Xem tiến trình ➔',
                actionUrl: `/orders/${cleanCode}`
            };
            triggerTitleBlink('Sạp đã nhận đơn!');
        } else if (st === 'cancelled' || st.includes('hủy')) {
            config = {
                icon: '🚫',
                title: 'ĐƠN HÀNG ĐÃ HỦY!',
                body: `Đơn hàng <strong>${orderCode}</strong> tại ${stallName} đã bị hủy.`,
                themeColor: '#dc2626',
                borderColor: '#f87171',
                actionText: 'Xem lại đơn hàng ➔',
                actionUrl: `/orders/${cleanCode}`
            };
            triggerTitleBlink('Đơn hàng đã hủy!');
        }

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.98));
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 2px solid ${config.borderColor};
            color: #ffffff;
            padding: 18px 20px;
            border-radius: 22px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.35);
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: auto;
            transform: translateX(120%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        `;

        toast.innerHTML = `
            <!-- Top glowing accent line -->
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3.5px; background: linear-gradient(90deg, #10b981, #38bdf8, #f59e0b);"></div>

            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: rgba(255,255,255,0.1); border: 1.5px solid ${config.borderColor}; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 14px rgba(0,0,0,0.2);">
                        ${config.icon}
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 0.95rem; color: ${config.borderColor}; letter-spacing: 0.3px;">
                            ${config.title}
                        </div>
                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.7); margin-top: 1px;">
                            ${orderCode} • ${stallName}
                        </div>
                    </div>
                </div>
                <button type="button" style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;" onclick="this.closest('div').parentElement.remove()">&times;</button>
            </div>

            <div style="background: rgba(255,255,255,0.06); border-radius: 14px; padding: 12px 16px; border: 1px solid rgba(255,255,255,0.08); font-size: 0.86rem; line-height: 1.5; color: rgba(255,255,255,0.9);">
                <div>${config.body}</div>
                ${config.badgeCode ? `
                    <div style="margin-top: 8px; background: rgba(56, 189, 248, 0.15); border: 1.5px dashed #38bdf8; border-radius: 10px; padding: 6px 12px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 0.76rem; color: #bae6fd; font-weight: 700;">MÃ HẸN LẤY ĐỒ:</span>
                        <strong style="font-family: monospace; font-size: 1.1rem; color: #ffffff; letter-spacing: 1px;">${config.badgeCode}</strong>
                    </div>
                ` : ''}
            </div>

            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 2px;">
                <a href="${config.actionUrl}" style="background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; font-weight: 800; font-size: 0.82rem; padding: 9px 18px; border-radius: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='none'">
                    ${config.actionText}
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
                setTimeout(() => toast.remove(), 400);
            }
        }, 12000);
    }

    // 5. Real-time Live Page Synchronization (Checkout Success / Orders Detail)
    function syncCurrentPageUI(order, newStatus) {
        const st = String(newStatus).toLowerCase();

        // A. Checkout Success Page (/checkout/success/*)
        const successBadge = document.getElementById(`status-badge-${order.id}`);
        if (successBadge) {
            if (st === 'ready' || st.includes('sẵn sàng') || st.includes('chờ lấy')) {
                successBadge.className = 'status-badge-pill status-ready';
                successBadge.style.cssText = 'background: #e0f2fe; color: #0369a1; border: 1.5px solid #7dd3fc; font-weight: 800; padding: 6px 16px; border-radius: 20px; box-shadow: 0 0 14px rgba(56, 189, 248, 0.4); animation: pulse-ready 1.5s infinite;';
                successBadge.innerHTML = '🏪 Sẵn sàng tại sạp (Chờ lấy)';
            } else if (st === 'completed' || st.includes('hoàn thành')) {
                successBadge.className = 'status-badge-pill status-completed';
                successBadge.style.cssText = 'background: #dcfce7; color: #15803d; border: 1.5px solid #86efac; font-weight: 800; padding: 6px 16px; border-radius: 20px;';
                successBadge.innerHTML = '✅ Đã hoàn thành';
            } else if (st === 'cancelled' || st.includes('hủy')) {
                successBadge.className = 'status-badge-pill status-cancelled';
                successBadge.style.cssText = 'background: #fee2e2; color: #b91c1c; border: 1.5px solid #fca5a5; font-weight: 800; padding: 6px 16px; border-radius: 20px;';
                successBadge.innerHTML = '❌ Đã hủy';
            } else if (st === 'confirmed' || st.includes('chuẩn bị')) {
                successBadge.className = 'status-badge-pill status-confirmed';
                successBadge.style.cssText = 'background: #fef3c7; color: #92400e; border: 1.5px solid #fde68a; font-weight: 800; padding: 6px 16px; border-radius: 20px;';
                successBadge.innerHTML = '👨‍🍳 Đang chuẩn bị đồ';
            }
        }

        // B. Orders Detail Page (/orders/{id})
        if (typeof renderOrderDetail === 'function' && document.getElementById('order-detail-container')) {
            const currentOrderId = document.getElementById('order-detail-container').getAttribute('data-order-id');
            if (currentOrderId && (currentOrderId.includes(String(order.id)) || currentOrderId === order.order_code)) {
                if (typeof loadOrderDetail === 'function') {
                    loadOrderDetail(currentOrderId);
                }
            }
        }
    }

    // 6. Polling Logic for Customer Orders
    async function pollCustomerOrders() {
        try {
            const res = await fetch(API_URL, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) return;

            const data = await res.json();
            if (!data || !data.data) return;

            const orders = data.data;

            if (isFirstPoll) {
                orders.forEach(o => {
                    knownOrderStatusMap[o.id] = o.status;
                });
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(knownOrderStatusMap));
                } catch (_) {}
                isFirstPoll = false;
                return;
            }

            orders.forEach(order => {
                const oldStatus = knownOrderStatusMap[order.id];
                const newStatus = order.status;

                if (oldStatus && oldStatus !== newStatus) {
                    // Trạng thái đơn hàng thay đổi!
                    knownOrderStatusMap[order.id] = newStatus;
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(knownOrderStatusMap));
                    } catch (_) {}

                    playCustomerChime();
                    showCustomerOrderToast(order, oldStatus, newStatus);
                    syncCurrentPageUI(order, newStatus);
                } else if (!oldStatus) {
                    knownOrderStatusMap[order.id] = newStatus;
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(knownOrderStatusMap));
                    } catch (_) {}
                }
            });
        } catch (err) {
            // Silently ignore network poll error
        }
    }

    // 7. Global Test Helper
    window.donganhTestCustomerToast = function (status = 'ready') {
        playCustomerChime();
        showCustomerOrderToast({
            id: 30,
            order_code: 'ORD030',
            order_code_full: '#ORD000030',
            status: status,
            status_label: status === 'ready' ? 'Sẵn sàng tại sạp' : 'Hoàn thành',
            eatery_name: 'Chợ Mạch Tràng - Gian hàng Rau củ Cô Vui',
            total_amount: 20000
        }, 'confirmed', status);
    };

    // 8. Start Polling Immediately
    function init() {
        pollCustomerOrders();
        setInterval(pollCustomerOrders, POLL_INTERVAL_MS);
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', init);
    }

})();
