    <!-- Shopping Cart Sidebar Drawer & Toast Container -->
    <div id="cartToastContainer" style="position: fixed; top: 90px; right: 24px; z-index: 9999999; display: flex; flex-direction: column; gap: 8px; pointer-events: none;"></div>

    <style>
        #cartDrawer { font-family: 'Plus Jakarta Sans', sans-serif; }
        #cartDrawerList { gap: 0; }
        .cart-group-header {
            background: linear-gradient(135deg, rgba(0, 168, 107, 0.08), rgba(0, 168, 107, 0.04));
            border-bottom: 1px solid rgba(0, 168, 107, 0.15);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: 0.2px;
        }
        .cart-item-row {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(0,0,0,0.035);
            transition: background 0.15s;
        }
        .cart-item-row:last-child { border-bottom: none; }
        .cart-item-row:hover { background: rgba(0, 168, 107, 0.025); }
        .cart-group-block {
            border: 1px solid rgba(0, 168, 107, 0.15);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 12px;
            background: var(--bg-card, #fff);
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .cart-item-img {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .cart-qty-btn {
            border: none;
            background: rgba(0, 168, 107, 0.1);
            color: var(--primary, #00A86B);
            cursor: pointer;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            flex-shrink: 0;
        }
        .cart-qty-btn:hover { background: var(--primary, #00A86B); color: white; }
        .cart-select-all-bar {
            background: linear-gradient(135deg, rgba(0, 168, 107, 0.06), rgba(0, 168, 107, 0.02));
            border: 1.5px solid rgba(0, 168, 107, 0.18);
            border-radius: 14px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            cursor: pointer;
        }
        .cart-checkbox {
            accent-color: var(--primary, #00A86B);
            cursor: pointer;
            width: 17px;
            height: 17px;
            margin: 0;
            flex-shrink: 0;
        }
        @keyframes cartFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cart-group-block { animation: cartFadeIn 0.2s ease both; }
        .cart-toast-item {
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            gap: 8px;
            pointer-events: auto;
            min-width: 240px;
            border: 1px solid rgba(255,255,255,0.2);
        }
    </style>

    <div id="cartDrawerOverlay" onclick="toggleCartDrawer()" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); backdrop-filter: blur(6px); z-index: 999998; opacity: 0; transition: opacity 0.3s ease;"></div>

    <div id="cartDrawer" style="position: fixed; top: 0; right: -440px; width: 440px; max-width: 100vw; height: 100vh; background: var(--bg-card, #f8f9fa); z-index: 999999; box-shadow: -20px 0 60px rgba(0,0,0,0.15); transition: right 0.35s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column;">

        <!-- Premium Drawer Header -->
        <div style="padding: 0; background: linear-gradient(135deg, #00C897 0%, #00A86B 100%); position: relative; overflow: hidden; flex-shrink: 0;">
            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.08);"></div>
            <div style="position: absolute; bottom: -30px; left: 20px; width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.06);"></div>
            <div style="padding: 20px 24px; position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                        🛒 Giỏ hàng mua sắm
                    </h3>
                    <p id="cartHeaderSubtitle" style="margin: 4px 0 0 0; font-size: 0.78rem; color: rgba(255,255,255,0.8); font-weight: 500;">Đang tải...</p>
                </div>
                <button onclick="toggleCartDrawer()" style="background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 50%; color: #ffffff; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s; backdrop-filter: blur(4px);" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    ✕
                </button>
            </div>
        </div>

        <!-- Drawer Body -->
        <div id="cartDrawerBody" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; scrollbar-width: thin; scrollbar-color: rgba(0, 168, 107, 0.3) transparent;">
            <!-- Loading -->
            <div id="cartDrawerLoading" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; color: var(--text-muted); gap: 14px;">
                <div style="width: 48px; height: 48px; border: 3px solid rgba(0, 168, 107, 0.15); border-top-color: #00A86B; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <span style="font-size: 0.88rem; font-weight: 600;">Đang tải giỏ hàng...</span>
            </div>
            <!-- Empty state -->
            <div id="cartDrawerEmpty" style="display: none; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 16px; text-align: center; color: var(--text-muted); padding: 20px;">
                <div style="width: 96px; height: 96px; border-radius: 50%; background: linear-gradient(135deg, rgba(0, 168, 107, 0.1), rgba(0, 168, 107, 0.05)); display: flex; align-items: center; justify-content: center; font-size: 3rem;">🛒</div>
                <div>
                    <h4 style="margin: 0 0 6px 0; color: var(--text-main); font-weight: 800; font-size: 1.05rem;">Giỏ hàng trống</h4>
                    <p style="margin: 0; font-size: 0.82rem; max-width: 240px; line-height: 1.6;">Hãy thêm các món ngon từ thực đơn nhà hàng hoặc đặc sản OCOP Đông Anh nhé!</p>
                </div>
                <button onclick="toggleCartDrawer()" style="border: 2px solid rgba(0, 168, 107, 0.3); background: rgba(0, 168, 107, 0.05); color: #00A86B; padding: 10px 24px; border-radius: 12px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,168,107,0.1)'" onmouseout="this.style.background='rgba(0,168,107,0.05)'">
                    Khám phá ngay →
                </button>
            </div>
            <!-- Item List wrapper -->
            <div id="cartDrawerList" style="display: none; flex-direction: column;"></div>
        </div>

        <!-- Premium Drawer Footer -->
        <div id="cartDrawerFooter" style="display: none; flex-direction: column; flex-shrink: 0; background: var(--bg-card, #ffffff); border-top: 1px solid rgba(0,0,0,0.06); box-shadow: 0 -8px 24px rgba(0,0,0,0.06);">
            <!-- Total summary -->
            <div style="padding: 14px 20px 10px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.78rem; font-weight: 600; display: block;">Tổng tiền đã chọn</span>
                    <strong id="cartDrawerTotal" style="color: #FF7A00; font-size: 1.4rem; font-weight: 900; font-family: var(--font-heading); letter-spacing: -0.5px;">0đ</strong>
                </div>
                <div id="cartSelectedInfo" style="text-align: right; font-size: 0.78rem; color: var(--text-muted); font-weight: 600;"></div>
            </div>
            <!-- Action buttons -->
            <div style="padding: 0 16px 16px 16px; display: flex; gap: 10px;">
                <button onclick="clearCart()" style="flex: 1; padding: 12px 8px; font-size: 0.82rem; border-radius: 12px; font-weight: 700; border: 1.5px solid rgba(239, 68, 68, 0.25); color: #ef4444; background: rgba(239, 68, 68, 0.04); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.borderColor='rgba(239,68,68,0.5)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.04)'; this.style.borderColor='rgba(239,68,68,0.25)'">
                    🗑️ Xóa tất cả
                </button>
                <a href="/checkout" id="cartDrawerCheckoutBtn" style="flex: 2; padding: 12px 8px; font-size: 0.88rem; border-radius: 12px; text-align: center; text-decoration: none; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: linear-gradient(135deg, #FF9F43, #FF7A00); color: white; box-shadow: 0 6px 20px rgba(255, 122, 0, 0.3); transition: all 0.2s; border: none;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 8px 28px rgba(255,122,0,0.45)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 20px rgba(255,122,0,0.3)'">
                    🚀 Đặt hàng ngay
                </a>
            </div>
        </div>
    </div>

    <script>
        // Global Shopping Cart Client API
        let isCartDrawerOpen = false;

        function toggleCartDrawer() {
            const drawer = document.getElementById('cartDrawer');
            const overlay = document.getElementById('cartDrawerOverlay');
            if (!drawer || !overlay) return;

            isCartDrawerOpen = !isCartDrawerOpen;
            if (isCartDrawerOpen) {
                drawer.style.right = '0';
                overlay.style.display = 'block';
                setTimeout(() => overlay.style.opacity = '1', 10);
                loadCart();
            } else {
                drawer.style.right = '-440px';
                overlay.style.opacity = '0';
                setTimeout(() => overlay.style.display = 'none', 300);
            }
        }

        function showCartToast(message, type = 'success') {
            const container = document.getElementById('cartToastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'cart-toast-item';
            if (type === 'success') {
                toast.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
                toast.style.color = '#ffffff';
            } else {
                toast.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
                toast.style.color = '#ffffff';
            }
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            toast.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
            toast.innerHTML = (type === 'success' ? '<span style="font-size:1.1rem">✅</span>' : '<span style="font-size:1.1rem">⚠️</span>') + message;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            }, 10);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(120%)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 350);
            }, 3000);
        }

        function formatMoney(num) {
            return new Intl.NumberFormat('vi-VN').format(num) + ' đ';
        }

        function updateCartBadge(count) {
            const badge = document.getElementById('floatingCartCount');
            if (badge) {
                if (count > 0) {
                    badge.innerText = count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
            const headerBadge = document.getElementById('headerCartCount');
            if (headerBadge) {
                if (count > 0) {
                    headerBadge.innerText = count;
                    headerBadge.style.display = 'flex';
                } else {
                    headerBadge.style.display = 'none';
                }
            }
        }

        let selectedCartItems = [];
        let globalCartResponse = null;

        function toggleSelectAllCart(cb) {
            if (!globalCartResponse || !globalCartResponse.data) return;
            if (cb.checked) {
                selectedCartItems = globalCartResponse.data.map(item => item.id);
            } else {
                selectedCartItems = [];
            }
            renderGroupedCart();
        }

        function toggleSelectGroup(cb, groupKey) {
            if (!globalCartResponse || !globalCartResponse.data) return;
            const groupItemIds = globalCartResponse.data
                .filter(item => {
                    const eId = item.eatery_id || 0;
                    const itemKey = item.stall_name ? `${eId}_${item.stall_name}` : String(eId);
                    return String(itemKey) === String(groupKey);
                })
                .map(item => item.id);

            if (cb.checked) {
                groupItemIds.forEach(id => {
                    if (!selectedCartItems.includes(id)) {
                        selectedCartItems.push(id);
                    }
                });
            } else {
                selectedCartItems = selectedCartItems.filter(id => !groupItemIds.includes(id));
            }
            renderGroupedCart();
        }

        function toggleSelectItem(cb, itemId) {
            if (cb.checked) {
                if (!selectedCartItems.includes(itemId)) {
                    selectedCartItems.push(itemId);
                }
            } else {
                selectedCartItems = selectedCartItems.filter(id => id !== itemId);
            }
            renderGroupedCart();
        }

        function renderGroupedCart() {
            const list = document.getElementById('cartDrawerList');
            const footer = document.getElementById('cartDrawerFooter');
            const subtitle = document.getElementById('cartHeaderSubtitle');
            const selectedInfo = document.getElementById('cartSelectedInfo');
            if (!list || !globalCartResponse) return;

            list.innerHTML = '';

            const totalItemsCount = globalCartResponse.data.length;
            const selectedItemsCount = selectedCartItems.length;
            const isAllSelected = totalItemsCount > 0 && selectedItemsCount === totalItemsCount;
            const isIndeterminate = selectedItemsCount > 0 && selectedItemsCount < totalItemsCount;

            // Update header subtitle
            if (subtitle) {
                subtitle.textContent = `${totalItemsCount} sản phẩm • ${selectedItemsCount} đã chọn`;
            }

            // Render Select All Bar
            const selectAllHtml = `
                <div class="cart-select-all-bar" onclick="document.getElementById('selectAllCart').click()">
                    <input type="checkbox" id="selectAllCart" onchange="toggleSelectAllCart(this)" ${isAllSelected ? 'checked' : ''} class="cart-checkbox" onclick="event.stopPropagation()">
                    <label for="selectAllCart" style="cursor: pointer; user-select: none; font-size: 0.88rem; font-weight: 700; color: var(--text-main); flex: 1;" onclick="event.stopPropagation()">
                        ✅ Chọn tất cả <span style="color: var(--primary); font-weight: 800;">(${selectedItemsCount}/${totalItemsCount} món)</span>
                    </label>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', selectAllHtml);

            // Group items by eatery_id and stall_name
            const groups = {};
            globalCartResponse.data.forEach(item => {
                const eId = item.eatery_id || 0;
                const key = item.stall_name ? `${eId}_${item.stall_name}` : String(eId);
                if (!groups[key]) {
                    const name = item.stall_name ? `${item.eatery_name || 'Chợ'} - ${item.stall_name}` : (item.eatery_name || 'Khác');
                    groups[key] = { name: name, items: [] };
                }
                groups[key].items.push(item);
            });

            // Render groups with stagger animation
            let groupIndex = 0;
            Object.keys(groups).forEach(key => {
                const group = groups[key];
                const groupItemIds = group.items.map(item => item.id);
                const isGroupAllSelected = groupItemIds.every(id => selectedCartItems.includes(id));
                const groupSelectedCount = groupItemIds.filter(id => selectedCartItems.includes(id)).length;
                const delay = groupIndex * 0.06;

                let groupHtml = `
                    <div class="cart-group-block" style="animation-delay: ${delay}s;">
                        <div class="cart-group-header" onclick="document.getElementById('groupCb_${key}').click(); event.preventDefault();">
                            <input type="checkbox" id="groupCb_${key}" onchange="toggleSelectGroup(this, '${key}')" ${isGroupAllSelected ? 'checked' : ''} class="cart-checkbox" onclick="event.stopPropagation()">
                            <span style="flex: 1;">🏪 ${group.name}</span>
                            <span style="background: rgba(0, 168, 107, 0.12); color: var(--primary, #00A86B); font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 700; flex-shrink: 0;">
                                ${groupSelectedCount}/${group.items.length}
                            </span>
                        </div>
                `;

                group.items.forEach(item => {
                    const isChecked = selectedCartItems.includes(item.id);
                    groupHtml += `
                        <div class="cart-item-row" id="cart-row-${item.id}" style="${isChecked ? '' : 'opacity: 0.6;'}">
                            <input type="checkbox" onchange="toggleSelectItem(this, ${item.id})" ${isChecked ? 'checked' : ''} class="cart-checkbox">
                            <img src="${item.image}" class="cart-item-img" alt="${item.name}"
                                onerror="this.src='https://placehold.co/56x56/00A86B/ffffff?text=%F0%9F%9B%92'">
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 0.84rem; font-weight: 700; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 3px;" title="${item.name}">${item.name}</div>
                                <div style="font-size: 0.78rem; color: #FF7A00; font-weight: 800;">${formatMoney(item.price)}</div>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0;">
                                <div style="display: flex; align-items: center; gap: 6px; background: rgba(0, 168, 107, 0.06); border: 1px solid rgba(0, 168, 107, 0.15); border-radius: 8px; padding: 3px 6px;">
                                    <button class="cart-qty-btn" data-item="${item.id}" data-action="dec"
                                        onclick="updateQty(${item.id}, parseInt(document.getElementById('cart-qty-${item.id}').dataset.qty) - 1)">
                                        ${item.quantity > 1 ? '−' : '🗑'}
                                    </button>
                                    <span id="cart-qty-${item.id}" data-qty="${item.quantity}"
                                        style="font-size: 0.82rem; font-weight: 800; min-width: 18px; text-align: center; color: var(--text-main); display: inline-block; transition: transform 0.15s, color 0.15s;">
                                        ${item.quantity}
                                    </span>
                                    <button class="cart-qty-btn" data-item="${item.id}" data-action="inc"
                                        onclick="updateQty(${item.id}, parseInt(document.getElementById('cart-qty-${item.id}').dataset.qty) + 1)">+</button>
                                </div>
                                <strong id="cart-subtotal-${item.id}" style="font-size: 0.82rem; font-weight: 800; color: var(--text-main); display: inline-block; transition: transform 0.15s, color 0.15s;">${formatMoney(item.price * item.quantity)}</strong>
                            </div>
                        </div>
                    `;
                });

                groupHtml += `</div>`;
                list.insertAdjacentHTML('beforeend', groupHtml);
                groupIndex++;
            });

            list.style.display = 'flex';

            // Update selected total & footer
            const selectedTotal = calculateSelectedTotal();
            const totalEl = document.getElementById('cartDrawerTotal');
            if (totalEl) totalEl.innerText = formatMoney(selectedTotal);

            if (selectedInfo) {
                selectedInfo.innerHTML = selectedItemsCount > 0
                    ? `<span style="color: #00A86B; font-weight: 800;">${selectedItemsCount}</span> / ${totalItemsCount} món`
                    : '<span style="color: #ef4444;">Chưa chọn món</span>';
            }

            const checkoutBtn = document.getElementById('cartDrawerCheckoutBtn');
            if (checkoutBtn) {
                if (selectedItemsCount > 0) {
                    checkoutBtn.setAttribute('href', '/checkout?items=' + selectedCartItems.join(','));
                    checkoutBtn.style.pointerEvents = 'auto';
                    checkoutBtn.style.opacity = '1';
                    checkoutBtn.style.filter = 'none';
                } else {
                    checkoutBtn.setAttribute('href', '#');
                    checkoutBtn.style.pointerEvents = 'none';
                    checkoutBtn.style.opacity = '0.5';
                    checkoutBtn.style.filter = 'grayscale(0.8)';
                }
            }

            if (footer) footer.style.display = 'flex';
        }

        function calculateSelectedTotal() {
            let total = 0;
            if (globalCartResponse && globalCartResponse.data) {
                globalCartResponse.data.forEach(item => {
                    if (selectedCartItems.includes(item.id)) {
                        total += item.price * item.quantity;
                    }
                });
            }
            return total;
        }

        function loadCart() {
            const loading = document.getElementById('cartDrawerLoading');
            const empty = document.getElementById('cartDrawerEmpty');
            const list = document.getElementById('cartDrawerList');
            const footer = document.getElementById('cartDrawerFooter');

            if (loading) loading.style.display = 'flex';
            if (empty) empty.style.display = 'none';
            if (list) list.style.display = 'none';
            if (footer) footer.style.display = 'none';

            fetch('/cart')
                .then(res => res.json())
                .then(res => {
                    if (loading) loading.style.display = 'none';
                    updateCartBadge(res.count);
                    globalCartResponse = res;

                    if (res.data.length === 0) {
                        if (empty) empty.style.display = 'flex';
                        selectedCartItems = [];
                        const subtitle = document.getElementById('cartHeaderSubtitle');
                        if (subtitle) subtitle.textContent = 'Giỏ hàng đang trống';
                        return;
                    }

                    // Select all items if selectedCartItems is empty
                    if (selectedCartItems.length === 0) {
                        selectedCartItems = res.data.map(item => item.id);
                    } else {
                        // filter out any item IDs that might have been removed
                        const currentIds = res.data.map(item => item.id);
                        selectedCartItems = selectedCartItems.filter(id => currentIds.includes(id));
                    }

                    renderGroupedCart();
                })
                .catch(err => {
                    console.error('Error loading cart', err);
                    if (loading) loading.style.display = 'none';
                    showCartToast('Không thể kết nối giỏ hàng', 'error');
                });
        }

        function addToCart(e, btn) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const id = btn.getAttribute('data-id');
            const type = btn.getAttribute('data-type'); // 'dish' or 'ocop_product'
            
            const payload = {
                quantity: 1
            };
            if (type === 'dish') {
                payload.dish_id = id;
            } else {
                payload.ocop_product_id = id;
            }

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳...';

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (res.success) {
                    showCartToast('Đã thêm sản phẩm vào giỏ hàng');
                    updateCartBadge(res.count);
                    
                    // Micro-animation: wiggle cart icon
                    const cartIcon = document.querySelector('.header-action-btn[title="Giỏ hàng"]');
                    if (cartIcon) {
                        cartIcon.style.transform = 'scale(1.2) rotate(-10deg)';
                        setTimeout(() => cartIcon.style.transform = 'none', 200);
                    }
                } else {
                    showCartToast(res.message || 'Lỗi thêm vào giỏ hàng', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                console.error(err);
                showCartToast('Lỗi đường truyền kết nối', 'error');
            });
        }

        function animateValue(el, newText, color) {
            if (!el) return;
            el.style.transform = 'scale(1.35)';
            el.style.color = color || 'var(--primary)';
            setTimeout(() => {
                el.textContent = newText;
            }, 80);
            setTimeout(() => {
                el.style.transform = 'scale(1)';
                el.style.color = '';
            }, 220);
        }

        function updateQty(itemId, newQty) {
            if (newQty <= 0) {
                removeItem(itemId);
                return;
            }

            const qtyEl    = document.getElementById(`cart-qty-${itemId}`);
            const totalEl  = document.getElementById(`cart-subtotal-${itemId}`);
            const drawerTotal = document.getElementById('cartDrawerTotal');
            const subtitle = document.getElementById('cartHeaderSubtitle');
            const selInfo  = document.getElementById('cartSelectedInfo');
            const decBtn   = document.querySelector(`[data-item="${itemId}"][data-action="dec"]`);

            // Cập nhật data model
            if (globalCartResponse && globalCartResponse.data) {
                const item = globalCartResponse.data.find(i => i.id === itemId);
                if (item) {
                    item.quantity = newQty;
                    item.total   = item.price * newQty;
                    globalCartResponse.count = globalCartResponse.data.reduce((s, i) => s + i.quantity, 0);

                    // ---- Cập nhật DOM tại chỗ, KHÔNG re-render ----
                    if (qtyEl) {
                        qtyEl.dataset.qty = newQty;
                        animateValue(qtyEl, newQty, '#0ea5e9');
                    }
                    if (totalEl) animateValue(totalEl, formatMoney(item.price * newQty), '#0284c7');

                    // Cập nhật nút trừ: đổi icon nếu cần
                    if (decBtn) decBtn.textContent = newQty > 1 ? '−' : '🗑';

                    // Cập nhật tổng footer
                    const newTotal = calculateSelectedTotal();
                    if (drawerTotal) animateValue(drawerTotal, formatMoney(newTotal), '#0ea5e9');

                    // Cập nhật subtitle & selectedInfo
                    const total = globalCartResponse.data.length;
                    const sel   = selectedCartItems.length;
                    if (subtitle) subtitle.textContent = `${total} sản phẩm • ${sel} đã chọn`;
                    if (selInfo) selInfo.innerHTML = sel > 0
                        ? `<span style="color:var(--primary);font-weight:800">${sel}</span> / ${total} món`
                        : '<span style="color:#ef4444">Chưa chọn món</span>';

                    updateCartBadge(globalCartResponse.count);
                }
            }

            // Gửi lên server nền
            fetch(`/cart/update/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newQty })
            })
            .then(res => res.json())
            .then(res => { if (!res.success) { showCartToast(res.message || 'Lỗi cập nhật', 'error'); loadCart(); } })
            .catch(() => loadCart());
        }

        function removeItem(itemId) {
            // ===== OPTIMISTIC UPDATE: xóa khỏi UI ngay =====
            if (globalCartResponse && globalCartResponse.data) {
                const idx = globalCartResponse.data.findIndex(i => i.id === itemId);
                if (idx !== -1) {
                    globalCartResponse.data.splice(idx, 1);
                    selectedCartItems = selectedCartItems.filter(id => id !== itemId);
                    globalCartResponse.total = globalCartResponse.data.reduce((s, i) => s + i.price * i.quantity, 0);
                    globalCartResponse.count = globalCartResponse.data.reduce((s, i) => s + i.quantity, 0);
                    updateCartBadge(globalCartResponse.count);

                    if (globalCartResponse.data.length === 0) {
                        document.getElementById('cartDrawerList').style.display = 'none';
                        document.getElementById('cartDrawerFooter').style.display = 'none';
                        document.getElementById('cartDrawerEmpty').style.display = 'flex';
                        const subtitle = document.getElementById('cartHeaderSubtitle');
                        if (subtitle) subtitle.textContent = 'Giỏ hàng đang trống';
                    } else {
                        renderGroupedCart();
                    }
                }
            }

            // Gửi request xóa lên server nền
            fetch(`/cart/remove/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showCartToast('Đã xóa sản phẩm khỏi giỏ');
                } else {
                    loadCart(); // fallback reload nếu lỗi
                }
            })
            .catch(err => {
                console.error(err);
                loadCart();
            });
        }

        function clearCart() {
            if (!confirm('Bạn có chắc muốn xóa sạch giỏ hàng?')) return;
            fetch('/cart/clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showCartToast('Đã xóa toàn bộ giỏ hàng');
                    loadCart();
                }
            })
            .catch(err => console.error(err));
        }

        // 📤 Global Share Function
        function shareLocationPage(name) {
            const shareTitle = name ? name + ' — DongAnh Discovery' : document.title;
            const shareText = 'Khám phá ' + (name || 'địa điểm') + ' trên Bản đồ số Đông Anh!';
            const shareUrl = window.location.href;

            if (navigator.share) {
                navigator.share({
                    title: shareTitle,
                    text: shareText,
                    url: shareUrl
                }).catch(() => {});
            } else {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(shareUrl).then(() => {
                        showCartToast('Đã sao chép liên kết chia sẻ địa điểm!', 'success');
                    }).catch(() => fallbackCopyShareLink());
                } else {
                    fallbackCopyShareLink();
                }
            }
        }

        function fallbackCopyShareLink() {
            const dummy = document.createElement('input');
            document.body.appendChild(dummy);
            dummy.value = window.location.href;
            dummy.select();
            document.execCommand('copy');
            document.body.removeChild(dummy);
            showCartToast('Đã sao chép liên kết chia sẻ địa điểm!', 'success');
        }

        // ❤️ Global Save / Favorite Location Function
        function toggleSaveLocation(btn, slug, name) {
            let saved = JSON.parse(localStorage.getItem('saved_locations') || '[]');
            const index = saved.findIndex(item => item.slug === slug);
            
            let isSaved = false;
            if (index > -1) {
                saved.splice(index, 1);
                isSaved = false;
            } else {
                saved.push({ slug: slug, name: name, url: window.location.href, savedAt: new Date().toISOString() });
                isSaved = true;
            }
            
            localStorage.setItem('saved_locations', JSON.stringify(saved));
            updateSaveButtonUI(btn, isSaved);

            showCartToast(isSaved ? 'Đã lưu địa điểm vào danh sách yêu thích!' : 'Đã bỏ lưu địa điểm!', isSaved ? 'success' : 'warning');
        }

        function updateSaveButtonUI(btn, isSaved) {
            if (!btn) return;
            const iconSpan = btn.querySelector('.save-icon');
            const textSpan = btn.querySelector('.save-text');

            if (isSaved) {
                btn.classList.add('saved');
                btn.style.background = 'rgba(239, 68, 68, 0.12)';
                btn.style.color = '#ef4444';
                btn.style.borderColor = 'rgba(239, 68, 68, 0.4)';
                if (iconSpan) iconSpan.textContent = '💖';
                if (textSpan) textSpan.textContent = 'Đã lưu';
            } else {
                btn.classList.remove('saved');
                btn.style.background = 'var(--bg-card, #ffffff)';
                btn.style.color = 'var(--text-main, #0f172a)';
                btn.style.borderColor = 'var(--border-glow, #e2e8f0)';
                if (iconSpan) iconSpan.textContent = '❤️';
                if (textSpan) textSpan.textContent = 'Lưu lại';
            }
        }

        function initSaveButtonState(btn, slug) {
            if (!btn || !slug) return;
            const saved = JSON.parse(localStorage.getItem('saved_locations') || '[]');
            const isSaved = saved.some(item => item.slug === slug);
            updateSaveButtonUI(btn, isSaved);
        }

        // Initialize cart counts on boot
        document.addEventListener('DOMContentLoaded', () => {
            fetch('/cart')
                .then(res => res.json())
                .then(res => updateCartBadge(res.count))
                .catch(err => console.warn('Cart initialization skipped', err));
        });
    </script>

    <!-- Floating Dynamic Cart Button -->
    <div id="floatingCartButton" style="position: fixed; bottom: 24px; right: 24px; z-index: 99999; display: flex; align-items: center; justify-content: center;">
        <button onclick="toggleCartDrawer()" style="width: 56px; height: 56px; border-radius: 28px; background: linear-gradient(135deg, var(--primary, #ff7e29), #ef4444); border: none; color: #ffffff; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(255, 126, 41, 0.4); transition: transform 0.2s, box-shadow 0.2s; position: relative;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 12px 40px rgba(255, 126, 41, 0.5)'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 8px 30px rgba(255, 126, 41, 0.4)'">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span id="floatingCartCount" style="display: none; position: absolute; top: -2px; right: -2px; background: #ef4444; color: #ffffff; font-size: 0.72rem; font-weight: 800; min-width: 18px; height: 18px; border-radius: 9px; align-items: center; justify-content: center; padding: 0 4px; border: 2px solid #ffffff;">0</span>
        </button>
    </div>
