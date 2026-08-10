/**
 * Senior UI/UX Redesigned Orders Controller
 * grabFood, ShopeeFood, Apple, Airbnb design language
 */

document.addEventListener('DOMContentLoaded', function () {
    // CSRF helper
    function getCsrfToken() {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta) return tokenMeta.getAttribute('content');
        
        const tokenInput = document.querySelector('input[name="_token"]');
        if (tokenInput) return tokenInput.value;
        
        return '';
    }

    function showNotification(message, type = 'success') {
        if (typeof showCartToast === 'function') {
            showCartToast(message, type);
        } else {
            alert(message);
        }
    }

    // Dynamic global actions
    window.confirmOrderReceived = function (orderId) {
        if (!confirm('Bạn xác nhận đã nhận được đầy đủ hàng từ đơn này?')) return;
        
        fetch(`/api/orders/${orderId}/confirm-received`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Đã xác nhận nhận hàng thành công!', 'success');
                if (document.getElementById('orders-list-container')) {
                    loadOrdersList();
                } else if (document.getElementById('order-detail-container')) {
                    loadOrderDetail(orderId);
                }
            } else {
                showNotification(data.message || 'Không thể xác nhận nhận hàng.', 'error');
            }
        })
        .catch(err => {
            console.error('Error confirming received:', err);
            showNotification('Có lỗi xảy ra khi xác nhận nhận hàng.', 'error');
        });
    };

    window.returnOrder = function (orderId) {
        const reason = prompt('Vui lòng nhập lý do bạn muốn yêu cầu Hoàn hàng / Trả hàng:');
        if (reason === null) return;
        
        fetch(`/api/orders/${orderId}/return`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason || 'Khách hàng yêu cầu trả hàng' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Đã gửi yêu cầu hoàn hàng thành công!', 'success');
                if (document.getElementById('orders-list-container')) {
                    loadOrdersList();
                } else if (document.getElementById('order-detail-container')) {
                    loadOrderDetail(orderId);
                }
            } else {
                showNotification(data.message || 'Không thể gửi yêu cầu hoàn hàng.', 'error');
            }
        })
        .catch(err => {
            console.error('Error returning order:', err);
            showNotification('Có lỗi xảy ra khi gửi yêu cầu hoàn hàng.', 'error');
        });
    };

    window.cancelOrder = function (orderId) {
        const reason = prompt('Nhập lý do hủy đơn hàng (hoặc để trống):');
        if (reason === null) return;
        
        const btn = document.querySelector(`.btn-cancel[data-id="${orderId}"]`);
        if (btn) btn.disabled = true;

        fetch(`/api/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Đã hủy đơn hàng thành công!', 'success');
                if (document.getElementById('orders-list-container')) {
                    loadOrdersList();
                } else if (document.getElementById('order-detail-container')) {
                    loadOrderDetail(orderId);
                }
            } else {
                showNotification(data.message || 'Không thể hủy đơn hàng.', 'error');
                if (btn) btn.disabled = false;
            }
        })
        .catch(err => {
            console.error('Error cancelling order:', err);
            showNotification('Có lỗi xảy ra khi gửi yêu cầu hủy đơn.', 'error');
            if (btn) btn.disabled = false;
        });
    };

    window.reorderItems = function (orderId) {
        const btn = document.querySelector(`.btn-reorder[data-id="${orderId}"]`);
        if (btn) btn.disabled = true;

        fetch(`/api/orders/${orderId}/reorder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Đã đặt lại các món thành công!', 'success');
                
                if (typeof loadCart === 'function') {
                    loadCart();
                }
                
                if (data.triggerCartOpen && typeof toggleCartDrawer === 'function') {
                    if (typeof isCartDrawerOpen !== 'undefined') {
                        if (!isCartDrawerOpen) toggleCartDrawer();
                    } else {
                        toggleCartDrawer();
                    }
                }
                
                if (btn) btn.disabled = false;
            } else {
                showNotification(data.message || 'Không thể đặt lại món.', 'error');
                if (btn) btn.disabled = false;
            }
        })
        .catch(err => {
            console.error('Error reordering items:', err);
            showNotification('Có lỗi xảy ra khi đặt lại món.', 'error');
            if (btn) btn.disabled = false;
        });
    };

    // -------------------------------------------------------------
    // Page 1: List Page
    // -------------------------------------------------------------
    const listContainer = document.getElementById('orders-list-container');
    if (listContainer) {
        const searchInput = document.getElementById('search-order-input');
        const startDateInput = document.getElementById('filter-start-date');
        const endDateInput = document.getElementById('filter-end-date');
        const statusTabs = document.querySelectorAll('.pill-tab');
        const statsDashboard = document.getElementById('orders-stats-dashboard');
        
        let currentStatusFilter = 'all';

        // Filter event listeners
        if (searchInput) {
            searchInput.addEventListener('input', debounce(loadOrdersList, 300));
        }
        if (startDateInput) {
            startDateInput.addEventListener('change', loadOrdersList);
        }
        if (endDateInput) {
            endDateInput.addEventListener('change', loadOrdersList);
        }

        statusTabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                statusTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentStatusFilter = this.getAttribute('data-status');
                loadOrdersList();
            });
        });

        // Initial load
        loadOrdersList();

        // Real-time polling 10s cho danh sách đơn hàng phía Khách hàng
        setInterval(function() {
            let url = `/api/orders?status=${currentStatusFilter}`;
            if (searchInput && searchInput.value) url += `&search=${encodeURIComponent(searchInput.value)}`;
            if (startDateInput && startDateInput.value) url += `&start_date=${startDateInput.value}`;
            if (endDateInput && endDateInput.value) url += `&end_date=${endDateInput.value}`;

            fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(resData => {
                if (resData.success && resData.data) {
                    if (resData.stats && statsDashboard) renderStatsDashboard(resData.stats);
                    if (resData.data.length > 0) renderOrdersList(resData.data);
                }
            })
            .catch(() => {});
        }, 10000);

        function loadOrdersList() {
            // Render premium Apple-style Skeleton Loaders during fetch
            renderSkeletons();

            let url = `/api/orders?status=${currentStatusFilter}`;
            if (searchInput && searchInput.value) {
                url += `&search=${encodeURIComponent(searchInput.value)}`;
            }
            if (startDateInput && startDateInput.value) {
                url += `&start_date=${startDateInput.value}`;
            }
            if (endDateInput && endDateInput.value) {
                url += `&end_date=${endDateInput.value}`;
            }

            fetch(url, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success) {
                    // Render Dashboard stats
                    if (resData.stats && statsDashboard) {
                        renderStatsDashboard(resData.stats);
                    }

                    if (resData.data && resData.data.length > 0) {
                        renderOrdersList(resData.data);
                    } else {
                        renderEmptyOrders();
                    }
                } else {
                    renderErrorState();
                }
            })
            .catch(err => {
                console.error('Error fetching orders:', err);
                renderErrorState();
            });
        }

        function renderSkeletons() {
            let skeletons = '';
            for (let i = 0; i < 2; i++) {
                skeletons += `
                    <div class="order-card" style="opacity: 0.8; pointer-events: none;">
                        <div class="order-card-header" style="height: 48px; background: #fafafa; display: flex; align-items: center; justify-content: space-between; padding: 0 20px;">
                            <div style="width: 120px; height: 16px;" class="skeleton-box"></div>
                            <div style="width: 80px; height: 16px;" class="skeleton-box"></div>
                        </div>
                        <div class="order-card-body">
                            <div style="border-right: 1.5px dashed #E5E7EB; padding-right: 24px;">
                                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                                    <div style="width: 48px; height: 48px; border-radius: 10px;" class="skeleton-box"></div>
                                    <div style="flex: 1; display: flex; flex-direction: column; gap: 6px;">
                                        <div style="width: 60%; height: 16px;" class="skeleton-box"></div>
                                        <div style="width: 30%; height: 12px;" class="skeleton-box"></div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div style="width: 80%; height: 14px;" class="skeleton-box"></div>
                                <div style="width: 90%; height: 14px;" class="skeleton-box"></div>
                                <div style="width: 40%; height: 22px; margin-top: 8px;" class="skeleton-box"></div>
                            </div>
                        </div>
                    </div>
                `;
            }
            listContainer.innerHTML = skeletons;
        }

        function renderStatsDashboard(stats) {
            statsDashboard.innerHTML = `
                <!-- Total Orders -->
                <div class="stat-card stat-card-total">
                    <div class="stat-icon-wrapper stat-icon-emerald">
                        <span class="stat-icon">📦</span>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value text-emerald">${stats.total}</span>
                        <span class="stat-label">Tổng đơn hàng</span>
                    </div>
                </div>

                <!-- Processing -->
                <div class="stat-card stat-card-processing">
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <span class="stat-icon">⏳</span>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value text-blue">${stats.processing}</span>
                        <span class="stat-label">Đang xử lý</span>
                    </div>
                </div>

                <!-- Completed -->
                <div class="stat-card stat-card-completed">
                    <div class="stat-icon-wrapper stat-icon-green">
                        <span class="stat-icon">✅</span>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value text-green">${stats.completed}</span>
                        <span class="stat-label">Hoàn thành</span>
                    </div>
                </div>

                <!-- Total spent -->
                <div class="stat-card stat-card-spent">
                    <div class="stat-icon-wrapper stat-icon-orange">
                        <span class="stat-icon">💳</span>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value text-orange" style="font-size: 1.35rem;">${formatCurrency(stats.spent)}</span>
                        <span class="stat-label">Tổng chi tiêu</span>
                    </div>
                </div>
            `;

            // Update Counts on the Tab Pill buttons
            updatePillText('all', `Tất cả (${stats.total})`);
            updatePillText('completed', `Hoàn thành (${stats.completed})`);
        }

        function updatePillText(status, text) {
            const tab = document.querySelector(`.pill-tab[data-status="${status}"]`);
            if (tab) {
                // Keep the icon if exists
                const icon = status === 'all' ? '🌐 ' : '📦 ';
                tab.innerHTML = `${icon}${text}`;
            }
        }

        function renderOrdersList(orders) {
            let html = '';
            orders.forEach(order => {
                let itemsHtml = '';
                let totalItemsQty = 0;
                
                order.items.forEach(item => {
                    totalItemsQty += item.quantity;
                    const itemImg = item.image ? item.image : 'https://placehold.co/80x80/ffe3d1/d97706?text=🍔';
                    itemsHtml += `
                        <div class="item-row">
                            <div class="item-thumb-wrapper">
                                <img src="${itemImg}" alt="${item.name}" class="item-thumbnail">
                            </div>
                            <div class="item-details">
                                <div class="item-name">${item.name}</div>
                                <div class="item-qty-meta">
                                    Số lượng: <span class="item-qty-pill">${item.quantity}</span>
                                </div>
                            </div>
                            <div class="item-total-price">
                                ${formatCurrency(item.price * item.quantity)}
                            </div>
                        </div>
                    `;
                });

                // Financial Breakdown
                let breakdownHtml = `
                    <div class="financial-row">
                        <span>Tạm tính (${order.items.length} ${order.category_slug === 'dong-anh-market' ? 'sản phẩm' : 'món'})</span>
                        <span style="font-weight: 700;">${formatCurrency(order.subtotal)}</span>
                    </div>
                    <div class="financial-row">
                        <span>Phí giao hàng</span>
                        <span style="${order.shipping_fee === 0 ? 'color: #10B981; font-weight: 700;' : 'font-weight: 600;'}">${order.shipping_fee === 0 ? 'Miễn phí' : '+ ' + formatCurrency(order.shipping_fee)}</span>
                    </div>
                `;

                if (order.voucher_code) {
                    breakdownHtml += `
                        <div class="financial-row" style="color: #ef4444;">
                            <span style="display: flex; align-items: center; gap: 4px;">
                                Giảm giá voucher 
                                <span style="border: 1px dashed #FF7A00; color: #FF7A00; padding: 1px 4px; border-radius: 4px; font-size: 0.7rem; font-weight: 800;">${order.voucher_code}</span>
                            </span>
                            <strong>-${formatCurrency(order.discount)}</strong>
                        </div>
                    `;
                }

                breakdownHtml += `
                    <div class="financial-row total-amount-row" style="border-top: 1.5px dashed #e2e8f0; padding-top: 10px; margin-top: 8px;">
                        <span style="font-weight: 800; font-size: 0.95rem; color: #1e293b;">Thành tiền</span>
                        <span class="price-val" style="font-size: 1.35rem; font-weight: 900; color: #ea580c; font-family: var(--font-heading);">${formatCurrency(order.total_amount)}</span>
                    </div>
                `;

                // Stepper Timeline inside order card
                const miniProgressHtml = renderCardTimelineProgress(order.status, order.category_slug === 'dong-anh-market');

                // Format Datetime
                const formattedTime = order.created_at_formatted.replace(' ', ' • ');

                // Action Buttons
                let actionsHtml = '';
                if (order.payment_method === 'Online' && order.status === 'pending') {
                    actionsHtml += `
                        <a href="/checkout/payment/${order.id}" class="btn-premium-action btn-pay-now" style="background: linear-gradient(135deg, #f97316, #ea580c); color: white; border: none; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; gap: 6px; box-shadow: 0 4px 14px rgba(234, 88, 12, 0.35); padding: 8px 18px; border-radius: 12px; font-size: 0.82rem;">
                            📲 Quét mã QR Thanh toán
                        </a>
                    `;
                }

                if (order.status === 'shipping' || order.status === 'delivering' || order.status === 'processing' || order.status === 'paid' || order.status === 'confirmed') {
                    actionsHtml += `
                        <button class="btn-premium-action btn-confirm-received" onclick="confirmOrderReceived(${order.id})" style="background: rgba(16, 185, 129, 0.1); color: #059669; border: 1.5px solid rgba(16, 185, 129, 0.4); font-weight: 700; border-radius: 12px; padding: 8px 16px; font-size: 0.82rem;">
                            ✅ Đã nhận được hàng
                        </button>
                    `;
                }

                if (order.status === 'pending' || order.status === 'paid' || order.status === 'confirmed') {
                    actionsHtml += `
                        <button class="btn-premium-action btn-cancel" data-id="${order.id}" onclick="cancelOrder(${order.id})" style="background: #fff; color: #ef4444; border: 1.5px solid #fecaca; border-radius: 12px; font-weight: 700; padding: 8px 16px; font-size: 0.82rem;">
                            🗑️ Hủy đơn
                        </button>
                    `;
                }

                if (order.status === 'completed' || order.status === 'shipping' || order.status === 'delivering') {
                    actionsHtml += `
                        <button class="btn-premium-action btn-return" onclick="returnOrder(${order.id})" style="background: rgba(234, 88, 12, 0.08); color: #ea580c; border: 1.5px solid rgba(234, 88, 12, 0.3); font-weight: 700; border-radius: 12px; padding: 8px 16px; font-size: 0.82rem;">
                            ↩️ Yêu cầu hoàn hàng
                        </button>
                    `;
                }
                
                if (order.status === 'completed') {
                    if (order.is_reviewed) {
                        actionsHtml += `
                            <button class="btn-premium-action btn-reviewed" disabled style="background: #F1F5F9; color: #94A3B8; cursor: not-allowed; border: 1.5px solid #E2E8F0; opacity: 0.85; border-radius: 12px; padding: 8px 16px; font-size: 0.82rem;">
                                ✓ Đã đánh giá
                            </button>
                        `;
                    } else {
                        actionsHtml += `
                            <button class="btn-premium-action btn-review" 
                                    onclick="openReviewModal(event, ${order.id}, '${order.order_code}', ${order.eatery_id}, '${order.category_slug}', \`${order.eatery_name.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)" 
                                    style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; font-weight: 700; border-radius: 12px; padding: 8px 16px; font-size: 0.82rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                                ⭐ Đánh giá ngay
                            </button>
                        `;
                    }
                }

                if (order.status === 'completed' || order.status === 'cancelled' || order.status === 'returned') {
                    actionsHtml += `
                        <button class="btn-premium-action btn-reorder" data-id="${order.id}" onclick="reorderItems(${order.id})" style="background: #f8fafc; color: #0284c7; border: 1.5px solid #bae6fd; border-radius: 12px; font-weight: 700; padding: 8px 16px; font-size: 0.82rem;">
                            🔄 Đặt lại món
                        </button>
                    `;
                }

                // Add explicit detailed view action button
                actionsHtml += `
                    <a href="/orders/${order.order_code_full.replace('#', '')}" class="btn-premium-action btn-detail" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; text-decoration: none; border-radius: 12px; font-weight: 700; padding: 8px 18px; font-size: 0.82rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                        Xem chi tiết ➔
                    </a>
                `;

                // Check if this order is pickup or delivery
                const isPickup = order.shipping_address && order.shipping_address.includes('[Ghé sạp lấy đồ]');
                const pickupCode = `MCP-${order.id.toString().padStart(5, '0')}`;
                
                // Formatted address box
                let addressBoxHtml = '';
                if (isPickup) {
                    const cleanAddr = order.shipping_address.replace('[Ghé sạp lấy đồ]', '').trim();
                    addressBoxHtml = `
                        <div class="order-delivery-address" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.04)); border: 1.8px dashed #10b981; border-radius: 14px; padding: 12px 14px; margin-top: 6px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.06);">
                            <div style="font-weight: 800; color: #047857; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                                <span style="display: flex; align-items: center; gap: 5px;">🏪 NƠI NHẬN ĐỒ (GHÉ SẠP LẤY)</span>
                                <span style="font-family: monospace; font-size: 0.88rem; color: #047857; background: #d1fae5; padding: 2px 8px; border-radius: 6px; border: 1px solid #6ee7b7; font-weight: 900; letter-spacing: 0.5px;">${pickupCode}</span>
                            </div>
                            <span class="address-val" style="color: #0f172a; font-size: 0.84rem; font-weight: 600; line-height: 1.45; display: block;">${cleanAddr}</span>
                        </div>
                    `;
                } else {
                    addressBoxHtml = `
                        <div class="order-delivery-address" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.08), rgba(2, 132, 199, 0.04)); border: 1.8px dashed #0ea5e9; border-radius: 14px; padding: 12px 14px; margin-top: 6px; box-shadow: 0 2px 8px rgba(14, 165, 233, 0.06);">
                            <div style="font-weight: 800; color: #0369a1; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                                🛵 ĐỊA CHỈ GIAO TẬN NƠI
                            </div>
                            <span class="address-val" style="color: #0f172a; font-size: 0.84rem; font-weight: 600; line-height: 1.45; display: block;">${order.shipping_address}</span>
                        </div>
                    `;
                }

                // Market & Stall Chip
                let marketChip = '';
                let stallTitle = order.eatery_name;
                if (order.eatery_name && order.eatery_name.includes(' - ')) {
                    const parts = order.eatery_name.split(' - ');
                    marketChip = `<span class="market-badge-chip" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #6d28d9; border: 1px solid #c4b5fd; padding: 3px 8px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">🏛️ ${parts[0]}</span>`;
                    stallTitle = parts[1];
                } else {
                    marketChip = `<span class="market-badge-chip" style="background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0369a1; border: 1px solid #7dd3fc; padding: 3px 8px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">🏪 ${order.category_slug === 'dong-anh-market' ? 'Chợ Số' : 'Cơ sở'}</span>`;
                }

                // Build modern 60/40 card
                html += `
                    <div class="order-card fade-in" style="border-radius: 24px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06); margin-bottom: 24px; background: #ffffff; position: relative;">
                        
                        <!-- Left vibrant accent bar -->
                        <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: ${order.status === 'cancelled' ? '#ef4444' : (order.status === 'completed' ? '#10b981' : (order.payment_method === 'Online' ? 'linear-gradient(180deg, #0ea5e9, #6366f1)' : 'linear-gradient(180deg, #10b981, #f59e0b)'))};"></div>

                        <!-- Header: Tên Gian Hàng & Chợ nổi bật -->
                        <div class="order-card-header" style="padding: 16px 24px; background: linear-gradient(to right, #f8fafc, #ffffff); border-bottom: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <div style="display: flex; flex-direction: column; gap: 5px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    ${marketChip}
                                    <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px; font-family: var(--font-heading);">
                                        🛒 ${stallTitle}
                                    </h4>
                                    <span class="badge-status ${order.status_class}" style="font-size: 0.75rem; padding: 3px 10px; border-radius: 8px; font-weight: 700;">
                                        ${order.status_label}
                                    </span>
                                </div>
                                <div style="font-size: 0.78rem; color: #64748b; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span>Mã đơn: <strong style="font-family: monospace; color: #2563eb; font-size: 0.85rem;">#${order.order_code}</strong></span>
                                    <span>•</span>
                                    <span>Đặt lúc: ${formattedTime}</span>
                                    <span>•</span>
                                    <span>Phương thức: <strong style="color: #334155;">${order.payment_method === 'COD' ? '💵 Tiền mặt khi nhận' : '💳 Chuyển khoản VietQR'}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="order-card-body" style="padding: 24px; display: grid; grid-template-columns: 1.4fr 1fr; gap: 28px;">
                            <!-- Left Pane: items (60%) -->
                            <div class="order-items-pane">
                                <div class="pane-heading" style="font-weight: 800; font-size: 0.88rem; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px;">📦 Sản phẩm đặt mua (${totalItemsQty} ${order.category_slug === 'dong-anh-market' ? 'sản phẩm' : 'món'})</div>
                                ${itemsHtml}
                                
                                <!-- Progress Stepper -->
                                ${miniProgressHtml}
                            </div>

                            <!-- Right Pane: costs (40%) -->
                            <div class="order-info-pane" style="background: #fafafa; border-radius: 16px; padding: 18px; border: 1px solid #f1f5f9; display: flex; flex-direction: column; justify-content: space-between; gap: 14px;">
                                <div>
                                    <div class="pane-heading" style="font-weight: 800; font-size: 0.84rem; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">💵 Chi tiết thanh toán</div>
                                    <div class="financials-list">
                                        ${breakdownHtml}
                                    </div>
                                </div>

                                <div style="border-top: 1px solid #e2e8f0; padding-top: 10px;">
                                    ${addressBoxHtml}
                                </div>
                            </div>
                        </div>

                        <!-- Footer actions -->
                        <div class="order-card-footer" style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">
                            ${actionsHtml}
                        </div>

                    </div>
                `;
            });

            listContainer.innerHTML = html;
        }

        // Stepper for inside order card: Đã xác nhận → Đang chuẩn bị → Đang giao → Hoàn thành
        function renderCardTimelineProgress(status, isMarket = true) {
            if (status === 'cancelled') {
                return `
                    <div style="background: rgba(239, 68, 68, 0.04); border: 1px solid rgba(239, 68, 68, 0.1); border-radius: 10px; padding: 8px 12px; margin-top: 16px; font-size: 0.8rem; color: #EF4444; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                        <span>🚫</span> Đơn hàng đã bị từ chối / hủy.
                    </div>
                `;
            }

            let step1 = 'completed', step2 = '', step3 = '', step4 = '';
            let barWidth = '0%';

            if (status === 'pending') {
                step1 = 'active';
                barWidth = '0%';
            } else if (status === 'confirmed' || status === 'paid' || status === 'processing' || status === 'preparing') {
                step1 = 'completed';
                step2 = 'active';
                barWidth = '33.3%';
            } else if (status === 'ready' || status === 'shipping' || status === 'delivering') {
                step1 = 'completed';
                step2 = 'completed';
                step3 = 'active';
                barWidth = '66.6%';
            } else if (status === 'completed') {
                step1 = 'completed';
                step2 = 'completed';
                step3 = 'completed';
                step4 = 'completed';
                barWidth = '100%';
            }

            return `
                <div class="card-timeline-progress">
                    <div class="card-timeline-bar-active" style="width: ${barWidth};"></div>
                    
                    <div class="card-timeline-step ${step1}">
                        <div class="card-timeline-dot"></div>
                        <span class="card-timeline-label">Khách đặt</span>
                    </div>
                    <div class="card-timeline-step ${step2}">
                        <div class="card-timeline-dot"></div>
                        <span class="card-timeline-label">Sạp nhận</span>
                    </div>
                    <div class="card-timeline-step ${step3}">
                        <div class="card-timeline-dot"></div>
                        <span class="card-timeline-label">Chờ lấy</span>
                    </div>
                    <div class="card-timeline-step ${step4}">
                        <div class="card-timeline-dot"></div>
                        <span class="card-timeline-label">Hoàn thành</span>
                    </div>
                </div>
            `;
        }

        function renderEmptyOrders() {
            listContainer.innerHTML = `
                <div class="empty-orders-illustration">
                    <div class="empty-graphic-wrapper">
                        <div class="empty-graphic-circle"></div>
                        <div class="empty-graphic-icon">📦</div>
                    </div>
                    <h3 style="margin: 0; color: var(--text-main); font-weight: 800; font-size: 1.2rem; font-family: var(--font-heading);">
                        Không tìm thấy đơn hàng nào
                    </h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); max-width: 380px; line-height: 1.5;">
                        Không có đơn hàng nào khớp với điều kiện lọc hoặc từ khóa tìm kiếm của bạn. Hãy thử thay đổi bộ lọc nhé!
                    </p>
                    <button onclick="resetFilters()" class="btn-premium-action btn-detail" style="margin-top: 6px;">
                        🔄 Đặt lại bộ lọc
                    </button>
                </div>
            `;
        }

        function renderErrorState() {
            listContainer.innerHTML = `
                <div class="empty-orders-illustration" style="border-color: rgba(239, 68, 68, 0.2);">
                    <div style="font-size: 3rem; margin-bottom: 8px;">⚠️</div>
                    <h3 style="margin: 0; color: var(--danger); font-weight: 800; font-size: 1.1rem;">Đã xảy ra lỗi hệ thống</h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Không thể tải dữ liệu đơn hàng. Vui lòng thử lại sau.</p>
                </div>
            `;
        }

        window.resetFilters = function() {
            if (searchInput) searchInput.value = '';
            if (startDateInput) startDateInput.value = '';
            if (endDateInput) endDateInput.value = '';
            statusTabs.forEach(t => t.classList.remove('active'));
            const allTab = document.querySelector('.pill-tab[data-status="all"]');
            if (allTab) allTab.classList.add('active');
            currentStatusFilter = 'all';
            loadOrdersList();
        };
    }

    // -------------------------------------------------------------
    // Page 2: Details Page
    // -------------------------------------------------------------
    const detailContainer = document.getElementById('order-detail-container');
    if (detailContainer) {
        const orderId = detailContainer.getAttribute('data-order-id');
        let lastOrderStatus = null;

        if (orderId) {
            loadOrderDetail(orderId);
            // Real-time polling 5 giây để cập nhật trạng thái đơn ngay khi Seller thao tác
            setInterval(() => pollOrderDetailSilent(orderId), 5000);
        }

        function pollOrderDetailSilent(id) {
            fetch(`/api/orders/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success && resData.data) {
                    if (lastOrderStatus !== resData.data.status) {
                        lastOrderStatus = resData.data.status;
                        renderOrderDetail(resData.data);
                    }
                }
            })
            .catch(() => {});
        }

        function loadOrderDetail(id) {
            detailContainer.innerHTML = `
                <div style="text-align: center; padding: 80px 40px; color: var(--text-muted);">
                    <span style="display: inline-block; animation: spin 1s linear infinite; font-size: 2rem; margin-bottom: 12px;">⏳</span>
                    <div style="font-weight: 600;">Đang kết nối tải chi tiết đơn hàng...</div>
                </div>
            `;

            fetch(`/api/orders/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success && resData.data) {
                    lastOrderStatus = resData.data.status;
                    renderOrderDetail(resData.data);
                } else {
                    detailContainer.innerHTML = `
                        <div class="empty-orders-illustration" style="border-color: rgba(239, 68, 68, 0.2);">
                            <div style="font-size: 3rem; margin-bottom: 8px;">🚫</div>
                            <h3 style="margin: 0; color: var(--danger); font-weight: 800; font-size: 1.1rem;">Không tìm thấy đơn hàng</h3>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Đơn hàng này không tồn tại hoặc bạn không có quyền xem.</p>
                            <a href="/orders" class="btn-premium-action btn-detail" style="margin-top: 8px; text-decoration: none;">⬅️ Quay lại</a>
                        </div>
                    `;
                }
            })
            .catch(err => {
                console.error('Error fetching order detail:', err);
                detailContainer.innerHTML = `
                    <div class="empty-orders-illustration" style="border-color: rgba(239, 68, 68, 0.2);">
                        <div style="font-size: 3rem; margin-bottom: 8px;">⚠️</div>
                        <h3 style="margin: 0; color: var(--danger); font-weight: 800; font-size: 1.1rem;">Lỗi tải dữ liệu</h3>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Gặp sự cố khi kết nối máy chủ. Vui lòng tải lại trang.</p>
                    </div>
                `;
            });
        }

        function renderOrderDetail(order) {
            const stepperHtml = renderHorizontalStepper(order.status, order.category_slug === 'dong-anh-market');

            // Render Items List
            let itemsHtml = '';
            order.items.forEach(item => {
                const itemImg = item.image ? item.image : 'https://placehold.co/80x80/ffe3d1/d97706?text=🍔';
                const stallBadgeHtml = order.stall_name 
                    ? `<div style="font-size: 0.75rem; color: #d97706; font-weight: 700; margin-top: 3px; display: inline-flex; align-items: center; gap: 4px; background: #fef3c7; padding: 2px 8px; border-radius: 6px; border: 1px solid #fde68a;">
                        🏪 Gian hàng: ${order.stall_name}
                      </div>`
                    : '';
                itemsHtml += `
                    <div style="display: flex; gap: 16px; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--border-light);">
                        <img src="${itemImg}" alt="${item.name}" style="width: 54px; height: 54px; border-radius: 12px; object-fit: cover; border: 1px solid rgba(0,0,0,0.05); flex-shrink: 0;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${item.name}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                ${formatCurrency(item.price)} x ${item.quantity}
                            </div>
                            ${stallBadgeHtml}
                        </div>
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; text-align: right; flex-shrink: 0;">
                            ${formatCurrency(item.price * item.quantity)}
                        </div>
                    </div>
                `;
            });

            // Costs Breakdown
            let breakdownHtml = `
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                    <span>Tạm tính</span>
                    <span>${formatCurrency(order.subtotal)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                    <span>Phí vận chuyển</span>
                    <span style="${order.shipping_fee === 0 ? 'color: var(--success); font-weight: 700;' : ''}">${order.shipping_fee === 0 ? 'Miễn phí' : '+ ' + formatCurrency(order.shipping_fee)}</span>
                </div>
            `;

            if (order.voucher_code) {
                breakdownHtml += `
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            Giảm giá voucher
                            <span style="border: 1px dashed var(--secondary); color: var(--secondary); padding: 1px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 800;">${order.voucher_code}</span>
                        </span>
                        <span style="color: var(--danger); font-weight: 700;">-${formatCurrency(order.discount)}</span>
                    </div>
                `;
            }

            breakdownHtml += `
                <div style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 700; border-top: 1.5px dashed var(--border-light); padding-top: 12px; margin-top: 12px;">
                    <span style="color: var(--text-main);">Tổng thanh toán:</span>
                    <span style="color: var(--secondary); font-size: 1.45rem; font-family: var(--font-heading); font-weight: 800;">${formatCurrency(order.total_amount)}</span>
                </div>
            `;

            // Payment status badge
            const isPaid = order.payment_status === 'success' || order.status === 'paid' || order.status === 'completed';
            const paymentBadgeHtml = isPaid
                ? `<span style="background: rgba(34, 197, 94, 0.08); color: var(--success); font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(34, 197, 94, 0.2); margin-left: 8px;">ĐÃ THANH TOÁN</span>`
                : `<span style="background: rgba(107, 114, 128, 0.08); color: var(--text-muted); font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(107, 114, 128, 0.2); margin-left: 8px;">CHƯA THANH TOÁN</span>`;

            // Timeline Vertical State
            const timelineHtml = renderVerticalTimeline(order);

            // Action Buttons
            let headerActionsHtml = '';
            if (order.payment_method === 'Online' && order.status === 'pending') {
                headerActionsHtml += `
                    <a href="/checkout/payment/${order.id}" class="btn-premium-action btn-pay-now" style="background: #ff7e29; color: white; border: 1.5px solid #ff7e29; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; gap: 4px; box-shadow: 0 4px 10px rgba(255, 126, 41, 0.2); padding: 8px 16px; border-radius: 12px; font-size: 0.8rem;">
                        💳 Thanh toán QR
                    </a>
                `;
            }
            if (order.status === 'pending' || order.status === 'paid') {
                headerActionsHtml += `
                    <button class="btn-premium-action btn-cancel" data-id="${order.id}" onclick="cancelOrder(${order.id})">
                        ❌ Hủy đơn hàng
                    </button>
                `;
            }
            if (order.status === 'completed') {
                if (order.is_reviewed) {
                    headerActionsHtml += `
                        <button class="btn-premium-action btn-reviewed" disabled style="background: #F1F5F9; color: #94A3B8; cursor: not-allowed; border: 1.5px solid #E2E8F0; opacity: 0.85;">
                            ✓ Đã đánh giá
                        </button>
                    `;
                } else {
                    headerActionsHtml += `
                        <button class="btn-premium-action btn-review" 
                                onclick="openReviewModal(event, ${order.id}, '${order.order_code}', ${order.eatery_id}, '${order.category_slug}', \`${order.eatery_name.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)" 
                                style="background: rgba(0, 168, 107, 0.08); color: #00A86B; border: 1.5px solid rgba(0, 168, 107, 0.25);">
                            ⭐ Đánh giá ngay
                        </button>
                    `;
                }
            }

            if (order.status === 'completed' || order.status === 'cancelled') {
                headerActionsHtml += `
                    <button class="btn-premium-action btn-reorder" data-id="${order.id}" onclick="reorderItems(${order.id})">
                        🔄 Đặt lại món
                    </button>
                `;
            }

            let noticeBannerHtml = '';
            if (order.status === 'ready' || order.status === 'shipping' || order.status === 'delivering') {
                noticeBannerHtml = `
                    <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1.5px solid #7dd3fc; border-radius: 14px; padding: 14px 18px; margin-top: 6px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.08);">
                        <div style="font-size: 1.8rem;">🏪</div>
                        <div>
                            <div style="font-weight: 800; color: #0369a1; font-size: 0.95rem; margin-bottom: 2px;">
                                🏪 Đồ của bạn đã sẵn sàng tại sạp!
                            </div>
                            <div style="font-size: 0.84rem; color: #0284c7; line-height: 1.4;">
                                Gian hàng <strong>${order.stall_name || 'tại chợ'}</strong> đã chuẩn bị xong. Hãy ghé sạp và đọc Mã đơn <strong>#ORD${String(order.id).padStart(6, '0')}</strong> hoặc SĐT <strong>${order.customer_phone}</strong> để nhận đồ nhé!
                            </div>
                        </div>
                    </div>
                `;
            } else if (order.status === 'confirmed' || order.status === 'preparing') {
                noticeBannerHtml = `
                    <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1.5px solid #6ee7b7; border-radius: 14px; padding: 14px 18px; margin-top: 6px; display: flex; align-items: center; gap: 12px;">
                        <div style="font-size: 1.8rem;">👨‍🍳</div>
                        <div>
                            <div style="font-weight: 800; color: #065f46; font-size: 0.95rem; margin-bottom: 2px;">
                                ✅ Gian hàng đã nhận đơn & đang chuẩn bị đồ!
                            </div>
                            <div style="font-size: 0.84rem; color: #047857; line-height: 1.4;">
                                Chủ gian <strong>${order.stall_name || 'tại chợ'}</strong> đang đóng gói túi đồ cho bạn theo giờ hẹn.
                            </div>
                        </div>
                    </div>
                `;
            }

            // Build details layout
            detailContainer.innerHTML = `
                <!-- Stepper Progress Card -->
                <div class="details-glass-panel" style="display: flex; flex-direction: column; gap: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                        <a href="/orders" class="btn-premium-action btn-cancel" style="border-radius: 12px; font-size: 0.8rem;">
                            ⬅️ Quay lại danh sách
                        </a>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            ${headerActionsHtml}
                        </div>
                    </div>
                    ${stepperHtml}
                    ${noticeBannerHtml}
                </div>

                <!-- 2 Column Layout (60/40) -->
                <div class="details-grid-wrapper">
                    
                    <!-- Left Column (60%) -->
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        
                        <!-- Dishes List -->
                        <div class="details-glass-panel">
                            <h3 class="details-section-title">
                                ${order.category_slug === 'dong-anh-market' ? '🏪 Chi tiết sản phẩm đặt mua' : '🍽️ Chi tiết món ăn đặt mua'}
                            </h3>
                            <div style="display: flex; flex-direction: column;">
                                ${itemsHtml}
                            </div>
                        </div>

                        <!-- Summary Cost -->
                        <div class="details-glass-panel">
                            <h3 class="details-section-title">
                                💵 Tổng kết chi phí đơn hàng
                            </h3>
                            ${breakdownHtml}
                        </div>

                    </div>

                    <!-- Right Column (40%) -->
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        
                        <!-- Order info card -->
                        <div class="details-glass-panel">
                            <h3 class="details-section-title">
                                ℹ️ Thông tin vận chuyển & Đặt hàng
                            </h3>
                            <div style="display: flex; flex-direction: column; gap: 14px; font-size: 0.88rem; line-height: 1.4;">
                                <div>
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Mã đơn hàng</span>
                                    <strong style="color: var(--text-main); font-size: 1rem; font-family: var(--font-heading);">#ORD${String(order.id).padStart(6, '0')}</strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Ngày đặt</span>
                                    <span style="color: var(--text-main); font-weight: 600;">${order.created_at_formatted}</span>
                                </div>
                                <div style="border-top: 1px dashed var(--border-light); padding-top: 10px;">
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">🏪 Thuộc Chợ & Gian hàng</span>
                                    <strong style="color: #0369a1; font-size: 0.98rem; font-weight: 800;">${order.eatery_name}</strong>
                                </div>
                                <div style="border-top: 1px dashed var(--border-light); padding-top: 10px;">
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Người nhận</span>
                                    <strong style="color: var(--text-main); font-size: 0.92rem;">${order.customer_name}</strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Số điện thoại</span>
                                    <span style="color: var(--text-main); font-weight: 600;">${order.customer_phone}</span>
                                </div>
                                <div style="border-top: 1px dashed var(--border-light); padding-top: 10px;">
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                                        ${order.shipping_address && order.shipping_address.includes('[Ghé sạp lấy đồ]') ? '🏪 Nơi nhận đồ (Ghé sạp lấy)' : '📍 Địa chỉ nhận hàng'}
                                    </span>
                                    <span style="color: var(--text-main); line-height: 1.45; display: block; font-weight: 600;">
                                        ${order.shipping_address ? order.shipping_address.replace('[Ghé sạp lấy đồ]', '').trim() : ''}
                                    </span>
                                    ${order.shipping_address && order.shipping_address.includes('[Ghé sạp lấy đồ]') ? `
                                        <div style="background: rgba(16, 185, 129, 0.08); border: 1.8px dashed #10b981; border-radius: 12px; padding: 10px 14px; text-align: center; margin-top: 8px;">
                                            <span style="font-size: 0.72rem; color: #047857; font-weight: 700; text-transform: uppercase; display: block;">MÃ HẸN LẤY ĐỒ (PICKUP CODE)</span>
                                            <strong style="font-size: 1.45rem; color: #059669; font-family: monospace; letter-spacing: 1px; display: block; margin: 2px 0;">MCP-${String(order.id).padStart(5, '0')}</strong>
                                            <span style="font-size: 0.75rem; color: #64748b;">Đưa mã này cho chủ sạp khi đến nhận đồ</span>
                                        </div>
                                    ` : ''}
                                </div>
                                <div style="border-top: 1px dashed var(--border-light); padding-top: 10px;">
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Phương thức thanh toán</span>
                                    <span style="color: var(--text-main); font-weight: 700; display: flex; align-items: center; flex-wrap: wrap;">
                                        ${order.payment_method === 'COD' ? 'Tiền mặt khi nhận đồ (COD)' : 'Chuyển khoản VietQR'} ${paymentBadgeHtml}
                                    </span>
                                </div>
                                ${order.category_slug !== 'dong-anh-market' ? `
                                <div style="border-top: 1px dashed var(--border-light); padding-top: 10px;">
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">🚚 Đối tác vận chuyển</span>
                                    <span style="color: var(--text-main); font-weight: 600;">🚴‍♀️ Đông Anh Food Express</span>
                                </div>
                                ` : ''}
                                <div style="border-top: 1px dashed var(--border-light); padding-top: 10px;">
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Ghi chú của bạn</span>
                                    <span style="color: var(--text-main); font-style: italic;">"${order.notes || 'Không có ghi chú'}"</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status timeline card -->
                        <div class="details-glass-panel">
                            <h3 class="details-section-title">
                                🔄 Nhật ký trạng thái
                            </h3>
                            ${timelineHtml}
                        </div>

                    </div>

                </div>
            `;
        }

        function renderHorizontalStepper(status, isMarket = true) {
            // Luồng 4 bước chuẩn Chợ 4.0: Khách đặt -> Sạp nhận -> Chờ lấy tại sạp -> Hoàn thành
            const steps = [
                { key: 'placed', label: 'Khách đặt', icon: '📝', check: true },
                { key: 'confirmed', label: 'Sạp nhận đơn', icon: '📋', check: false },
                { key: 'ready', label: 'Chờ lấy tại sạp', icon: '🏪', check: false },
                { key: 'completed', label: 'Hoàn thành', icon: '🎉', check: false }
            ];

            if (status === 'pending') {
                steps[0].check = true;
            } else if (status === 'confirmed' || status === 'paid' || status === 'processing' || status === 'preparing') {
                steps[0].check = true;
                steps[1].check = true;
            } else if (status === 'ready' || status === 'shipping' || status === 'delivering') {
                steps[0].check = true;
                steps[1].check = true;
                steps[2].check = true;
            } else if (status === 'completed') {
                steps[0].check = true;
                steps[1].check = true;
                steps[2].check = true;
                steps[3].check = true;
            } else if (status === 'cancelled') {
                return `
                    <div style="background: rgba(239, 68, 68, 0.04); border: 1.5px solid rgba(239, 68, 68, 0.15); border-radius: 12px; padding: 16px; text-align: center; color: var(--danger); font-weight: 750; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>🚫</span> Đơn hàng đã bị từ chối / hủy.
                    </div>
                `;
            }

            let html = '<div class="stepper-horizontal" style="display: flex; justify-content: space-between; align-items: center; position: relative; padding: 10px 0;">';
            html += '<div style="position: absolute; top: 26px; left: 40px; right: 40px; height: 4px; background: #e2e8f0; z-index: 1; border-radius: 4px;">';
            let activeCount = steps.filter(s => s.check).length;
            let activePercentage = activeCount > 1 ? ((activeCount - 1) / (steps.length - 1)) * 100 : 0;
            html += `<div style="width: ${activePercentage}%; height: 100%; background: var(--primary); border-radius: 4px; transition: width 0.4s ease;"></div>`;
            html += '</div>';

            steps.forEach((step, index) => {
                const isActive = step.check;
                const circleBg = isActive ? 'var(--primary)' : '#e2e8f0';
                const circleTextColor = isActive ? '#ffffff' : '#94a3b8';
                const textWeight = isActive ? '700' : '500';
                const textColor = isActive ? 'var(--primary)' : '#94a3b8';
                const opacity = isActive ? '1' : '0.65';
                const glow = isActive ? 'box-shadow: 0 4px 12px rgba(0, 168, 107, 0.2);' : '';

                html += `
                    <div style="display: flex; flex-direction: column; align-items: center; width: 75px; z-index: 2; text-align: center; position: relative; opacity: ${opacity};">
                        <div style="width: 34px; height: 34px; border-radius: 50%; background: ${circleBg}; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; color: ${circleTextColor}; ${glow} margin-bottom: 6px; transition: all 0.25s ease;">
                            ${step.icon}
                        </div>
                        <span style="font-size: 0.78rem; font-weight: ${textWeight}; color: ${textColor}; white-space: nowrap;">
                            ${step.label}
                        </span>
                    </div>
                `;
            });

            html += '</div>';
            return html;
        }

        function renderVerticalTimeline(order) {
            const status = order.status;
            const time = order.created_at_formatted;
            const isMarket = order.category_slug === 'dong-anh-market';

            const events = [
                { title: isMarket ? 'Nhận hàng thành công' : 'Giao hàng thành công', time: 'Đang chờ...', active: false },
                { title: isMarket ? 'Sẵn sàng chờ khách lấy' : 'Đơn hàng đang giao', time: 'Đang chờ...', active: false },
                { title: 'Đơn hàng đã được chuẩn bị xong', time: 'Đang chuẩn bị...', active: false },
                { title: 'Đơn hàng đã được xác nhận', time: 'Đang chờ...', active: false },
                { title: 'Đặt đơn hàng thành công', time: time, active: true }
            ];

            if (status === 'completed') {
                events[0].active = true;
                events[0].time = time;
                events[1].active = true;
                events[1].time = time;
                events[2].active = true;
                events[2].time = time;
                events[3].active = true;
                events[3].time = time;
            } else if (status === 'shipping' || status === 'delivering') {
                events[1].active = true;
                events[1].time = time;
                events[2].active = true;
                events[2].time = time;
                events[3].active = true;
                events[3].time = time;
            } else if (status === 'paid' || status === 'processing') {
                events[2].active = true;
                events[2].time = time;
                events[3].active = true;
                events[3].time = time;
            } else if (status === 'confirmed') {
                events[3].active = true;
                events[3].time = time;
            } else if (status === 'cancelled') {
                events.unshift({ title: 'Đơn hàng bị hủy', time: time, active: true, isRed: true });
            }

            let html = '<div class="timeline-vertical" style="display: flex; flex-direction: column; gap: 20px; position: relative; padding-left: 18px; margin-top: 10px;">';
            html += '<div style="position: absolute; top: 6px; bottom: 6px; left: 5px; width: 2px; background: rgba(0,0,0,0.06); z-index: 1;"></div>';

            events.forEach(event => {
                const dotColor = event.active ? (event.isRed ? 'var(--danger)' : 'var(--primary)') : '#cbd5e1';
                const shadow = event.active ? (event.isRed ? '0 0 10px rgba(239,68,68,0.4)' : '0 0 10px rgba(0,168,107,0.3)') : 'none';
                const titleColor = event.active ? 'var(--text-main)' : '#94a3b8';
                const timeColor = event.active ? 'var(--text-muted)' : 'rgba(0,0,0,0.2)';
                const titleWeight = event.active ? '700' : '500';

                html += `
                    <div style="display: flex; align-items: flex-start; gap: 14px; z-index: 2; position: relative;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: ${dotColor}; box-shadow: ${shadow}; margin-top: 3px; flex-shrink: 0; transition: all 0.25s ease;"></div>
                        <div style="display: flex; flex-direction: column; gap: 2px; min-width: 0;">
                            <span style="font-size: 0.82rem; font-weight: ${titleWeight}; color: ${titleColor};">
                                ${event.title}
                            </span>
                            <span style="font-size: 0.72rem; color: ${timeColor};">
                                ${event.time}
                            </span>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            return html;
        }
    }

    // Currency Formatter
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount).replace('₫', 'đ');
    }

    // Debounce
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    let selectedFiles = [];

    // Global Review Modal Functions
    window.openReviewModal = function (event, orderId, orderCode, eateryId, categorySlug, eateryName) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        // Create modal if not exists
        let modal = document.getElementById('orderReviewModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'orderReviewModal';
            modal.className = 'review-modal-backdrop';
            modal.innerHTML = `
                <div class="review-modal-container">
                    <div class="review-modal-header">
                        <h3 class="review-modal-title">⭐ Đánh giá dịch vụ</h3>
                        <button class="review-modal-close" onclick="closeReviewModal()">&times;</button>
                    </div>
                    <div class="review-modal-body">
                        <div class="review-store-info">
                            <span class="review-store-icon">🏪</span>
                            <div class="review-store-details">
                                <span id="reviewStoreName" class="review-store-name">Tên cửa hàng</span>
                                <span id="reviewOrderCode" class="review-order-code">Mã đơn hàng</span>
                            </div>
                        </div>

                        <form id="orderReviewForm" onsubmit="event.preventDefault();">
                            <input type="hidden" id="reviewOrderId">
                            
                            <div class="review-section-label">Đánh giá của bạn</div>
                            <div class="review-star-rating-wrapper">
                                <div class="review-stars">
                                    <input type="radio" id="star5" name="rating" value="5" class="review-star-input">
                                    <label for="star5" class="review-star-label" title="Tuyệt vời! 5 sao">★</label>
                                    <input type="radio" id="star4" name="rating" value="4" class="review-star-input">
                                    <label for="star4" class="review-star-label" title="Rất hài lòng. 4 sao">★</label>
                                    <input type="radio" id="star3" name="rating" value="3" class="review-star-input">
                                    <label for="star3" class="review-star-label" title="Bình thường. 3 sao">★</label>
                                    <input type="radio" id="star2" name="rating" value="2" class="review-star-input">
                                    <label for="star2" class="review-star-label" title="Không hài lòng. 2 sao">★</label>
                                    <input type="radio" id="star1" name="rating" value="1" class="review-star-input">
                                    <label for="star1" class="review-star-label" title="Rất tệ. 1 sao">★</label>
                                </div>
                                <div id="reviewRatingHint" class="review-rating-hint">Chọn số sao</div>
                            </div>

                            <div class="review-comment-group">
                                <div class="review-section-label">Nhận xét của bạn</div>
                                <textarea id="reviewCommentText" class="review-comment-textarea" maxlength="500" placeholder="Chia sẻ trải nghiệm thực tế về món ăn, dịch vụ và vận chuyển tại đây nhé..."></textarea>
                                <div class="review-textarea-footer">
                                    <span id="reviewCommentCharCount">0</span>/500
                                </div>
                            </div>

                            <div class="review-media-group">
                                <div class="review-section-label">Hình ảnh / Video thực tế (Tùy chọn)</div>
                                <div class="review-media-upload-area" id="reviewDragArea">
                                    <span class="review-media-upload-icon">📸</span>
                                    <div class="review-media-upload-text">Chọn hoặc kéo thả ảnh/video vào đây</div>
                                    <div class="review-media-upload-subtext">Định dạng ảnh hoặc video ngắn (tối đa 5 file, < 20MB)</div>
                                    <input type="file" id="reviewMediaInput" class="review-media-input" multiple accept="image/*,video/*">
                                </div>
                                <div id="reviewMediaPreviewGrid" class="review-media-preview-grid"></div>
                            </div>
                        </form>
                    </div>
                    <div class="review-modal-footer">
                        <button class="review-btn-cancel" onclick="closeReviewModal()">Hủy bỏ</button>
                        <button id="reviewBtnSubmit" class="review-btn-submit" onclick="submitOrderReviewForm()" disabled>Gửi đánh giá</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Add interactive events
            setupReviewModalEvents(modal);
        }

        // Reset state
        selectedFiles = [];
        document.getElementById('orderReviewForm').reset();
        document.getElementById('reviewOrderId').value = orderId;
        document.getElementById('reviewStoreName').textContent = eateryName || 'Cửa hàng';
        document.getElementById('reviewOrderCode').textContent = `Đơn hàng #${orderCode}`;
        document.getElementById('reviewRatingHint').textContent = 'Chọn số sao';
        document.getElementById('reviewRatingHint').className = 'review-rating-hint';
        document.getElementById('reviewCommentCharCount').textContent = '0';
        document.getElementById('reviewMediaPreviewGrid').innerHTML = '';
        document.getElementById('reviewBtnSubmit').disabled = true;

        // Show modal
        setTimeout(() => modal.classList.add('active'), 10);
    };

    window.closeReviewModal = function () {
        const modal = document.getElementById('orderReviewModal');
        if (modal) {
            modal.classList.remove('active');
        }
    };

    function setupReviewModalEvents(modal) {
        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeReviewModal();
            }
        });

        // Stars hover and click logic
        const starsContainer = modal.querySelector('.review-stars');
        const hintEl = modal.querySelector('#reviewRatingHint');
        const submitBtn = modal.querySelector('#reviewBtnSubmit');
        const commentTextarea = modal.querySelector('#reviewCommentText');
        const charCountEl = modal.querySelector('#reviewCommentCharCount');
        const fileInput = modal.querySelector('#reviewMediaInput');
        const dragArea = modal.querySelector('#reviewDragArea');

        const hints = {
            '1': 'Rất tệ 😞',
            '2': 'Không hài lòng 😐',
            '3': 'Bình thường 😌',
            '4': 'Rất ngon / Hài lòng 🙂',
            '5': 'Tuyệt vời! 😍'
        };

        // Star click listener
        starsContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('review-star-input')) {
                const val = e.target.value;
                hintEl.textContent = hints[val];
                hintEl.classList.add('active');
                submitBtn.disabled = false;
            }
        });

        // Stars hover helper
        const starLabels = starsContainer.querySelectorAll('.review-star-label');
        starLabels.forEach((label, idx) => {
            const starVal = 5 - idx; // since elements are in reverse in DOM
            
            label.addEventListener('mouseenter', function() {
                hintEl.textContent = hints[starVal];
                hintEl.classList.add('active');
            });

            label.addEventListener('mouseleave', function() {
                const checkedRadio = starsContainer.querySelector('.review-star-input:checked');
                if (checkedRadio) {
                    hintEl.textContent = hints[checkedRadio.value];
                } else {
                    hintEl.textContent = 'Chọn số sao';
                    hintEl.classList.remove('active');
                }
            });
        });

        // Comment char count
        commentTextarea.addEventListener('input', function() {
            charCountEl.textContent = commentTextarea.value.length;
        });

        // Drag & drop file uploads
        ['dragenter', 'dragover'].forEach(eventName => {
            dragArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dragArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dragArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dragArea.classList.remove('dragover');
            }, false);
        });

        dragArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        });

        fileInput.addEventListener('change', function() {
            handleFiles(fileInput.files);
        });

        function handleFiles(files) {
            const maxFiles = 5;
            const maxSizeBytes = 20 * 1024 * 1024; // 20MB

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                if (selectedFiles.length >= maxFiles) {
                    showNotification('Chỉ được chọn tối đa 5 file ảnh hoặc video.', 'error');
                    break;
                }

                if (file.size > maxSizeBytes) {
                    showNotification(`File ${file.name} vượt quá dung lượng cho phép (20MB).`, 'error');
                    continue;
                }

                // Add to array
                selectedFiles.push(file);
            }

            renderPreviews();
        }

        function renderPreviews() {
            const previewGrid = modal.querySelector('#reviewMediaPreviewGrid');
            previewGrid.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'review-media-preview-item';

                const removeBtn = document.createElement('button');
                removeBtn.className = 'review-media-preview-remove';
                removeBtn.innerHTML = '&times;';
                removeBtn.onclick = (e) => {
                    e.preventDefault();
                    selectedFiles.splice(index, 1);
                    renderPreviews();
                };

                const url = URL.createObjectURL(file);

                if (file.type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.src = url;
                    video.muted = true;
                    video.autoplay = false;
                    video.controls = false;
                    previewItem.appendChild(video);
                } else {
                    const img = document.createElement('img');
                    img.src = url;
                    previewItem.appendChild(img);
                }

                previewItem.appendChild(removeBtn);
                previewGrid.appendChild(previewItem);
            });
        }
    }

    window.submitOrderReviewForm = function () {
        const modal = document.getElementById('orderReviewModal');
        const submitBtn = modal.querySelector('#reviewBtnSubmit');
        const orderId = modal.querySelector('#reviewOrderId').value;
        const ratingInput = modal.querySelector('.review-star-input:checked');
        const commentText = modal.querySelector('#reviewCommentText').value;

        if (!ratingInput) {
            showNotification('Vui lòng chọn số sao đánh giá.', 'error');
            return;
        }

        const ratingVal = ratingInput.value;

        // Change button state to loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-mini"></span> Gửi đánh giá...';

        const formData = new FormData();
        formData.append('rating', ratingVal);
        formData.append('comment', commentText);
        selectedFiles.forEach((file, index) => {
            formData.append('media[]', file);
        });

        fetch(`/api/orders/${orderId}/review`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Đánh giá đơn hàng thành công!', 'success');
                closeReviewModal();
                // Refresh list or detail page dynamically
                if (document.getElementById('orders-list-container')) {
                    loadOrdersList();
                } else if (document.getElementById('order-detail-container')) {
                    loadOrderDetail(orderId);
                }
            } else {
                showNotification(data.message || 'Có lỗi xảy ra khi gửi đánh giá.', 'error');
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Gửi đánh giá';
            }
        })
        .catch(err => {
            console.error('Error submitting review:', err);
            showNotification('Lỗi kết nối máy chủ. Vui lòng thử lại sau.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Gửi đánh giá';
        });
    };
});
