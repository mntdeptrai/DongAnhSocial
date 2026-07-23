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
    window.cancelOrder = function (orderId) {
        if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) return;
        
        const btn = document.querySelector(`.btn-cancel[data-id="${orderId}"]`);
        if (btn) btn.disabled = true;

        fetch(`/api/orders/${orderId}/cancel`, {
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
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <span class="stat-value">${stats.total}</span>
                    <span class="stat-label">Tổng đơn hàng</span>
                    <svg class="sparkline-svg" viewBox="0 0 100 30">
                        <path d="M0 25 L15 18 L30 22 L45 10 L60 14 L75 8 L90 12 L100 5 L100 30 L0 30 Z" fill="rgba(0, 168, 107, 0.05)" stroke="#00A86B" stroke-width="1.5"></path>
                    </svg>
                </div>

                <!-- Processing -->
                <div class="stat-card">
                    <div class="stat-icon" style="color: #2563eb !important;">⏳</div>
                    <span class="stat-value">${stats.processing}</span>
                    <span class="stat-label">Đang xử lý</span>
                    <svg class="sparkline-svg" viewBox="0 0 100 30">
                        <path d="M0 20 L20 22 L40 18 L60 8 L80 14 L100 5 L100 30 L0 30 Z" fill="rgba(59, 130, 246, 0.05)" stroke="#2563eb" stroke-width="1.5"></path>
                    </svg>
                </div>

                <!-- Completed -->
                <div class="stat-card">
                    <div class="stat-icon" style="color: #22C55E !important;">✅</div>
                    <span class="stat-value">${stats.completed}</span>
                    <span class="stat-label">Hoàn thành</span>
                    <svg class="sparkline-svg" viewBox="0 0 100 30">
                        <path d="M0 22 L15 25 L30 18 L45 20 L60 12 L75 5 L90 8 L100 2 L100 30 L0 30 Z" fill="rgba(34, 197, 94, 0.05)" stroke="#22C55E" stroke-width="1.5"></path>
                    </svg>
                </div>

                <!-- Total spent -->
                <div class="stat-card">
                    <div class="stat-icon" style="color: #FF7A00 !important;">💳</div>
                    <span class="stat-value" style="font-size: 1.35rem;">${formatCurrency(stats.spent)}</span>
                    <span class="stat-label">Tổng chi tiêu</span>
                    <svg class="sparkline-svg" viewBox="0 0 100 30">
                        <path d="M0 25 L20 20 L40 22 L60 10 L80 8 L100 2 L100 30 L0 30 Z" fill="rgba(255, 122, 0, 0.05)" stroke="#FF7A00" stroke-width="1.5"></path>
                    </svg>
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
                            <img src="${itemImg}" alt="${item.name}" class="item-thumbnail">
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
                        <span>${formatCurrency(order.subtotal)}</span>
                    </div>
                    <div class="financial-row">
                        <span>Phí giao hàng</span>
                        <span style="${order.shipping_fee === 0 ? 'color: #22C55E; font-weight: 700;' : ''}">${order.shipping_fee === 0 ? 'Miễn phí' : '+ ' + formatCurrency(order.shipping_fee)}</span>
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
                    <div class="financial-row total-amount-row">
                        <span>Thành tiền</span>
                        <span class="price-val">${formatCurrency(order.total_amount)}</span>
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
                        <a href="/checkout/payment/${order.id}" class="btn-premium-action btn-pay-now" style="background: #ff7e29; color: white; border: 1.5px solid #ff7e29; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; font-weight: 700; gap: 4px; box-shadow: 0 4px 10px rgba(255, 126, 41, 0.2); padding: 8px 16px; border-radius: 12px; font-size: 0.8rem;">
                            💳 Thanh toán QR
                        </a>
                    `;
                }
                if (order.status === 'pending' || order.status === 'paid') {
                    actionsHtml += `
                        <button class="btn-premium-action btn-cancel" data-id="${order.id}" onclick="cancelOrder(${order.id})">
                            ❌ Hủy đơn
                        </button>
                    `;
                }
                
                if (order.status === 'completed') {
                    if (order.is_reviewed) {
                        actionsHtml += `
                            <button class="btn-premium-action btn-reviewed" disabled style="background: #F1F5F9; color: #94A3B8; cursor: not-allowed; border: 1.5px solid #E2E8F0; opacity: 0.85;">
                                ✓ Đã đánh giá
                            </button>
                        `;
                    } else {
                        actionsHtml += `
                            <button class="btn-premium-action btn-review" 
                                    onclick="openReviewModal(event, ${order.id}, '${order.order_code}', ${order.eatery_id}, '${order.category_slug}', \`${order.eatery_name.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`)" 
                                    style="background: rgba(0, 168, 107, 0.08); color: #00A86B; border: 1.5px solid rgba(0, 168, 107, 0.25);">
                                ⭐ Đánh giá ngay
                            </button>
                        `;
                    }
                }

                if (order.status === 'completed' || order.status === 'cancelled') {
                    actionsHtml += `
                        <button class="btn-premium-action btn-reorder" data-id="${order.id}" onclick="reorderItems(${order.id})">
                            🔄 Đặt lại món
                        </button>
                    `;
                }

                // Add explicit detailed view action button
                actionsHtml += `
                    <a href="/orders/${order.id}" class="btn-premium-action btn-detail">
                        Xem chi tiết ➔
                    </a>
                `;

                // Build modern 60/40 card
                html += `
                    <div class="order-card fade-in">
                        
                        <!-- Header -->
                        <div class="order-card-header">
                            <div class="order-code-title">
                                <span>🛒</span> #${order.order_code}
                                <span class="badge-status ${order.status_class}">
                                    ${order.status_label}
                                </span>
                            </div>
                            <div class="order-meta-info">
                                <span>${formattedTime}</span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="order-card-body">
                            <!-- Left Pane: items (60%) -->
                            <div class="order-items-pane">
                                <div class="pane-heading">📦 Sản phẩm đặt mua (${totalItemsQty} ${order.category_slug === 'dong-anh-market' ? 'sản phẩm' : 'món'})</div>
                                ${itemsHtml}
                                
                                <!-- Progress Stepper -->
                                ${miniProgressHtml}
                            </div>

                            <!-- Right Pane: costs (40%) -->
                            <div class="order-info-pane">
                                <div>
                                    <div class="pane-heading">💵 Chi tiết chi phí</div>
                                    <div class="financials-list">
                                        ${breakdownHtml}
                                    </div>
                                </div>

                                <div style="border-top: 1px solid rgba(0,0,0,0.04); padding-top: 12px;">
                                    <div class="order-delivery-address">
                                        📍 ĐỊA CHỈ GIAO HÀNG
                                        <span class="address-val">${order.shipping_address}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer actions -->
                        <div class="order-card-footer">
                            ${actionsHtml}
                        </div>

                    </div>
                `;
            });

            listContainer.innerHTML = html;
        }

        // Stepper for inside order card: Đã xác nhận → Đang chuẩn bị → Đang giao → Hoàn thành
        function renderCardTimelineProgress(status, isMarket = false) {
            if (status === 'cancelled') {
                return `
                    <div style="background: rgba(239, 68, 68, 0.04); border: 1px solid rgba(239, 68, 68, 0.1); border-radius: 10px; padding: 8px 12px; margin-top: 16px; font-size: 0.8rem; color: #EF4444; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                        <span>🚫</span> Đơn hàng đã bị hủy.
                    </div>
                `;
            }

            let step1 = 'completed', step2 = '', step3 = '', step4 = '';
            let barWidth = '0%';

            if (status === 'pending') {
                step1 = 'active';
                barWidth = '0%';
            } else if (status === 'paid' || status === 'processing') {
                step1 = 'completed';
                step2 = 'active';
                barWidth = '33.3%';
            } else if (status === 'shipping' || status === 'delivering') {
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
                        <span class="card-timeline-label">Xác nhận</span>
                    </div>
                    <div class="card-timeline-step ${step2}">
                        <div class="card-timeline-dot"></div>
                        <span class="card-timeline-label">Chuẩn bị</span>
                    </div>
                    <div class="card-timeline-step ${step3}">
                        <div class="card-timeline-dot"></div>
                        <span class="card-timeline-label">${isMarket ? 'Chờ lấy hàng' : 'Đang giao'}</span>
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
        if (orderId) {
            loadOrderDetail(orderId);
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
                itemsHtml += `
                    <div style="display: flex; gap: 16px; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--border-light);">
                        <img src="${itemImg}" alt="${item.name}" style="width: 54px; height: 54px; border-radius: 12px; object-fit: cover; border: 1px solid rgba(0,0,0,0.05); flex-shrink: 0;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${item.name}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                ${formatCurrency(item.price)} x ${item.quantity}
                            </div>
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
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Người nhận</span>
                                    <strong style="color: var(--text-main); font-size: 0.92rem;">${order.customer_name}</strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Số điện thoại</span>
                                    <span style="color: var(--text-main); font-weight: 600;">${order.customer_phone}</span>
                                </div>
                                <div style="border-top: 1px dashed var(--border-light); padding-top: 10px;">
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">📍 Địa chỉ nhận hàng</span>
                                    <span style="color: var(--text-main); line-height: 1.4; display: block; word-break: break-all; font-weight: 500;">${order.shipping_address}</span>
                                </div>
                                <div style="border-top: 1px dashed var(--border-light); padding-top: 10px;">
                                    <span style="color: var(--text-muted); font-weight: 700; display: block; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Phương thức thanh toán</span>
                                    <span style="color: var(--text-main); font-weight: 700; display: flex; align-items: center; flex-wrap: wrap;">
                                        ${order.payment_method} ${paymentBadgeHtml}
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

        function renderHorizontalStepper(status, isMarket = false) {
            const steps = [
                { key: 'placed', label: 'Đặt đơn', icon: '📝', check: true },
                { key: 'confirmed', label: 'Xác nhận', icon: '📋', check: false },
                { key: 'preparing', label: 'Chuẩn bị', icon: '🍳', check: false },
                { key: 'shipping', label: isMarket ? 'Chờ lấy hàng' : 'Đang giao', icon: isMarket ? '🏪' : '🚴', check: false },
                { key: 'completed', label: 'Hoàn thành', icon: '📦', check: false }
            ];

            if (status === 'pending') {
                steps[0].check = true;
            } else if (status === 'paid' || status === 'processing') {
                steps[0].check = true;
                steps[1].check = true;
                steps[2].check = true;
            } else if (status === 'shipping' || status === 'delivering') {
                steps[0].check = true;
                steps[1].check = true;
                steps[2].check = true;
                steps[3].check = true;
            } else if (status === 'completed') {
                steps[0].check = true;
                steps[1].check = true;
                steps[2].check = true;
                steps[3].check = true;
                steps[4].check = true;
            } else if (status === 'cancelled') {
                return `
                    <div style="background: rgba(239, 68, 68, 0.04); border: 1.5px solid rgba(239, 68, 68, 0.15); border-radius: 12px; padding: 16px; text-align: center; color: var(--danger); font-weight: 750; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>🚫</span> Đơn hàng đã bị hủy.
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
