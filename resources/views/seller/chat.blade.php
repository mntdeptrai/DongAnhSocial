@extends('layouts.seller')

@section('title', 'Trò Chuyện & Nhắn Tin Khách Hàng — ' . $stallName)

@section('content')

<!-- Workspace Header -->
<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div>
        <h1 style="font-family: var(--slr-font); font-size: 1.55rem; font-weight: 800; margin: 0; color: var(--slr-text-main); display: flex; align-items: center; gap: 8px;">
            <span>💬</span> Trò Chuyện & Nhắn Tin Khách Hàng
        </h1>
        <p style="font-size: 0.88rem; color: var(--slr-text-muted); margin-top: 4px;">
            Giao tiếp trực tiếp, tư vấn món ngon & phản hồi tin nhắn của khách hàng tại <strong>{{ $stallName }}</strong>.
        </p>
    </div>

    <!-- Live connection status badge -->
    <div id="seller-chat-status" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 20px; font-size: 0.78rem; font-weight: 700; color: #065f46;">
        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; animation: pulse-dot 1.5s infinite;"></span>
        <span>Kênh kết nối trực tuyến</span>
    </div>
</div>

<!-- Main Chat Container -->
<div class="seller-chat-layout" style="display: grid; grid-template-columns: 340px 1fr; gap: 20px; height: calc(100vh - 220px); min-height: 580px;">

    <!-- Left Column: Conversations List -->
    <div class="admin-card" style="display: flex; flex-direction: column; height: 100%; padding: 0; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        
        <!-- Room / Filter Tabs -->
        <div style="padding: 14px 16px; background: linear-gradient(135deg, #fffbeb, #fef3c7); border-bottom: 1px solid #fde68a; display: flex; gap: 6px;">
            <button type="button" id="tabBtnPrivate" onclick="switchSellerRoom('private')" style="flex: 1; padding: 8px 12px; border-radius: 12px; border: none; font-size: 0.82rem; font-weight: 800; cursor: pointer; transition: all 0.2s; background: #d97706; color: #ffffff; box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);">
                📩 Khách Nhắn Riêng
            </button>
            <button type="button" id="tabBtnPublic" onclick="switchSellerRoom('public')" style="flex: 1; padding: 8px 12px; border-radius: 12px; border: 1px solid #fde68a; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: all 0.2s; background: #ffffff; color: #78350f;">
                🏛️ Chat Chung Chợ
            </button>
        </div>

        <!-- Search Bar -->
        <div style="padding: 12px 16px; border-bottom: 1px solid #f3f4f6;">
            <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 0.85rem; color: #9ca3af;">🔍</span>
                <input type="text" id="convSearchInput" oninput="filterConversations()" placeholder="Tìm khách theo tên, SĐT..." style="width: 100%; padding: 8px 12px 8px 34px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 0.82rem; font-family: var(--slr-font); outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
        </div>

        <!-- Conversations Scroll List -->
        <div id="conversationsListContainer" style="flex: 1; overflow-y: auto; padding: 8px 10px; display: flex; flex-direction: column; gap: 6px;">
            <div style="text-align: center; padding: 40px 16px; color: #9ca3af; font-size: 0.85rem;">
                <div style="font-size: 2rem; margin-bottom: 8px;">⏳</div>
                Đang tải danh sách hội thoại...
            </div>
        </div>
    </div>

    <!-- Right Column: Active Chat Area -->
    <div class="admin-card" style="display: flex; flex-direction: column; height: 100%; padding: 0; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); position: relative;">

        <!-- Active Chat Header -->
        <div id="activeChatHeader" style="padding: 14px 20px; background: #ffffff; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="headerAvatar" style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #d97706, #b45309); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 4px 10px rgba(217, 119, 6, 0.25);">
                    👤
                </div>
                <div>
                    <h3 id="headerTitle" style="font-size: 1rem; font-weight: 800; color: var(--slr-text-main); margin: 0;">
                        Chọn cuộc trò chuyện
                    </h3>
                    <p id="headerSubtitle" style="font-size: 0.78rem; color: var(--slr-text-muted); margin: 2px 0 0 0;">
                        Tin nhắn riêng tư với khách hàng tại gian hàng
                    </p>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div id="headerActions" style="display: flex; align-items: center; gap: 8px;">
                <a id="btnCallCustomer" href="#" style="display: none; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 12px; background: #ecfdf5; color: #059669; font-weight: 700; font-size: 0.78rem; text-decoration: none; border: 1px solid #a7f3d0;">
                    📞 Gọi khách
                </a>
                <a id="btnViewOrders" href="/seller/orders" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 12px; background: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.78rem; text-decoration: none; border: 1px solid #fde68a;">
                    🛍️ Đơn hàng
                </a>
            </div>
        </div>

        <!-- Messages Feed Container -->
        <div id="sellerMessagesWindow" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 14px; background: #fafafa;">
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #9ca3af; text-align: center; padding: 30px;">
                <div style="font-size: 3rem; margin-bottom: 12px;">💬</div>
                <strong style="font-size: 1.05rem; color: #374151; margin-bottom: 4px;">Chào mừng bạn đến với Kênh Trò Chuyện Gian Hàng</strong>
                <p style="font-size: 0.85rem; color: #6b7280; max-width: 380px;">Chọn một khách hàng ở danh sách bên trái để xem tin nhắn và phản hồi tư vấn ngay tức thì.</p>
            </div>
        </div>

        <!-- Attachment Previews -->
        <div id="sellerProductPreview" style="display: none; padding: 8px 16px; background: #fffbeb; border-top: 1px dashed #fde68a;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #ffffff; padding: 8px 12px; border-radius: 10px; border: 1px solid #fde68a;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2rem;">🏷️</span>
                    <div>
                        <strong id="sellerAttachedProdName" style="font-size: 0.82rem; color: #92400e;">Tên món</strong>
                        <span id="sellerAttachedProdPrice" style="font-size: 0.76rem; color: #d97706; font-weight: 800; margin-left: 8px;">0đ</span>
                    </div>
                </div>
                <button type="button" onclick="clearAttachedProduct()" style="background: none; border: none; font-size: 1rem; color: #ef4444; cursor: pointer;">✕</button>
            </div>
            <input type="hidden" id="sellerAttachedProductId" value="">
        </div>

        <div id="sellerImagePreview" style="display: none; padding: 8px 16px; background: #ecfdf5; border-top: 1px dashed #a7f3d0;">
            <div style="display: flex; align-items: center; gap: 10px; position: relative; width: fit-content;">
                <img id="sellerAttachedImgElem" src="" style="max-width: 80px; max-height: 80px; border-radius: 8px; object-fit: cover; border: 1.5px solid #10b981;">
                <button type="button" onclick="clearAttachedImage()" style="position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: #ef4444; color: #fff; border: none; font-size: 0.7rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>
            </div>
        </div>

        <!-- Chat Input Form & Toolbar -->
        <div style="padding: 12px 18px 16px; background: #ffffff; border-top: 1px solid #f3f4f6; display: flex; flex-direction: column; gap: 10px;">
            
            <!-- Canned Quick Replies (Phản hồi nhanh 1 chạm của chủ sạp) -->
            <div style="display: flex; align-items: center; gap: 6px; overflow-x: auto; padding-bottom: 2px;">
                <span style="font-size: 0.72rem; font-weight: 800; color: #9ca3af; text-transform: uppercase; flex-shrink: 0;">⚡ Trả lời nhanh:</span>
                <button type="button" class="seller-quick-pill" onclick="sendSellerQuickReply('Dạ sạp em còn hàng ạ! Mời anh/chị ghé lấy nhé.')">Dạ còn hàng ạ!</button>
                <button type="button" class="seller-quick-pill" onclick="sendSellerQuickReply('Dạ giá sản phẩm niêm yết đúng giá trên hệ thống ạ.')">Chuẩn giá niêm yết</button>
                <button type="button" class="seller-quick-pill" onclick="sendSellerQuickReply('Sạp em đã đóng gói sẵn sàng túi đồ cho anh/chị rồi ạ!')">Đã gói xong đồ</button>
                <button type="button" class="seller-quick-pill" onclick="sendSellerQuickReply('Cảm ơn anh/chị đã tin tưởng ủng hộ gian hàng của em!')">Cảm ơn quý khách</button>
            </div>

            <!-- Input Bar -->
            <form id="sellerChatForm" onsubmit="handleSellerChatSubmit(event)" style="display: flex; gap: 10px; align-items: center;">
                <!-- Product Attachment Dropdown Trigger -->
                <div style="position: relative;">
                    <button type="button" onclick="toggleProductAttachDropdown()" title="Gắn sản phẩm của sạp" style="padding: 10px 14px; background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 14px; color: #d97706; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                        🏷️ <span style="display: none; @media(min-width: 768px){ display: inline; }">Gắn Món</span>
                    </button>
                    <!-- Products Dropdown -->
                    <div id="sellerProductAttachDropdown" style="display: none; position: absolute; bottom: calc(100% + 10px); left: 0; width: 280px; max-height: 240px; overflow-y: auto; background: #ffffff; border-radius: 16px; border: 1.5px solid #fde68a; box-shadow: 0 12px 35px rgba(0,0,0,0.15); z-index: 100; padding: 6px;">
                        <div style="padding: 8px 12px; font-size: 0.75rem; font-weight: 800; color: #92400e; border-bottom: 1px solid #fef3c7;">
                            Chọn món để gắn vào tin nhắn:
                        </div>
                        @if(isset($products) && $products->isNotEmpty())
                            @foreach($products as $p)
                                <div onclick="selectStallProduct({{ $p->id }}, '{{ addslashes($p->name) }}', {{ (float)$p->price }})" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border-radius: 10px; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='transparent'">
                                    <strong style="font-size: 0.82rem; color: #1f2937;">{{ $p->name }}</strong>
                                    <span style="font-size: 0.78rem; font-weight: 800; color: #059669;">{{ number_format($p->price, 0, ',', '.') }}đ</span>
                                </div>
                            @endforeach
                        @else
                            <div style="padding: 14px; font-size: 0.8rem; color: #9ca3af; text-align: center;">Chưa có món nào</div>
                        @endif
                    </div>
                </div>

                <!-- Image Attachment Button -->
                <button type="button" onclick="document.getElementById('sellerChatImageInput').click()" title="Đính kèm hình ảnh thực tế" style="padding: 10px 14px; background: #f3f4f6; border: 1.5px solid #e5e7eb; border-radius: 14px; color: #4b5563; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    📷
                </button>
                <input type="file" id="sellerChatImageInput" style="display: none;" accept="image/*" onchange="handleSellerImageSelected(this)">

                <!-- Text Input -->
                <input type="text" id="sellerMsgInput" placeholder="Nhập tin nhắn tư vấn khách hàng..." style="flex: 1; padding: 11px 16px; border-radius: 14px; border: 1.5px solid #e5e7eb; font-size: 0.88rem; font-family: var(--slr-font); outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#d97706'" onblur="this.style.borderColor='#e5e7eb'" required autocomplete="off">

                <!-- Submit Button -->
                <button type="submit" id="btnSendSellerMsg" style="padding: 11px 22px; background: linear-gradient(135deg, #d97706, #b45309); color: #ffffff; font-weight: 800; font-size: 0.86rem; border: none; border-radius: 14px; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 14px rgba(217, 119, 6, 0.3); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                    Gửi ➔
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.seller-quick-pill {
    font-size: 0.74rem;
    font-weight: 700;
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
    padding: 4px 10px;
    border-radius: 16px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
    font-family: var(--slr-font);
}
.seller-quick-pill:hover {
    background: #d97706;
    color: #ffffff;
    border-color: #d97706;
}
.conv-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1.5px solid transparent;
    background: #ffffff;
}
.conv-item:hover {
    background: #fdf6ec;
    border-color: #fde68a;
}
.conv-item.active {
    background: #fffbeb !important;
    border-color: #d97706 !important;
    box-shadow: 0 4px 14px rgba(217, 119, 6, 0.12);
}

.chat-bubble-seller-row {
    display: flex;
    gap: 10px;
    max-width: 75%;
}
.chat-bubble-seller-row.own {
    align-self: flex-end;
    flex-direction: row-reverse;
}
.chat-bubble-seller-row.other {
    align-self: flex-start;
}
.chat-bubble-box {
    padding: 10px 16px;
    border-radius: 18px;
    font-size: 0.88rem;
    line-height: 1.45;
    word-break: break-word;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.chat-bubble-seller-row.own .chat-bubble-box {
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #ffffff;
    border-bottom-right-radius: 4px;
}
.chat-bubble-seller-row.other .chat-bubble-box {
    background: #ffffff;
    color: #1f2937;
    border: 1px solid #e5e7eb;
    border-bottom-left-radius: 4px;
}

@media(max-width: 900px) {
    .seller-chat-layout {
        grid-template-columns: 1fr !important;
        height: auto !important;
    }
}
</style>

@endsection

@section('scripts')
<script>
const DEFAULT_PRESELECTED_CUSTOMER_ID = {{ $selectedCustomerId ? (int)$selectedCustomerId : 'null' }};
let currentRoom = 'private'; // 'private' or 'public'
let currentCustomerId = DEFAULT_PRESELECTED_CUSTOMER_ID;
let currentCustomerName = '';
let currentCustomerPhone = '';
let allConversations = [];
let chatPollTimer = null;
let lastMsgId = 0;

// 1. Fetch & Render Conversations
async function fetchConversations() {
    try {
        const res = await fetch('/seller/api/chat/conversations', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.success) {
            allConversations = data.conversations;
            renderConversationsList();

            // Auto select first customer if none selected yet
            if (currentRoom === 'private' && !currentCustomerId && allConversations.length > 0) {
                selectConversation(allConversations[0].customer_id, allConversations[0].customer_name, allConversations[0].customer_phone);
            }
        }
    } catch (e) {
        console.warn('Error fetching conversations:', e);
    }
}

function renderConversationsList(filterQuery = '') {
    const container = document.getElementById('conversationsListContainer');
    if (!container) return;

    if (currentRoom === 'public') {
        container.innerHTML = `
            <div class="conv-item active" style="border-color: #d97706;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; flex-shrink: 0;">
                    🏛️
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong style="font-size: 0.86rem; color: #1f2937;">Phòng Chat Chung</strong>
                        <span style="font-size: 0.7rem; color: #10b981; font-weight: 700;">Trực tuyến</span>
                    </div>
                    <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">
                        Giao lưu cộng đồng & toàn bộ bà con tại chợ
                    </div>
                </div>
            </div>
        `;
        return;
    }

    let filtered = allConversations;
    if (filterQuery.trim()) {
        const q = filterQuery.toLowerCase();
        filtered = allConversations.filter(c => c.customer_name.toLowerCase().includes(q) || c.customer_phone.includes(q));
    }

    if (filtered.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px 16px; color: #9ca3af; font-size: 0.85rem;">
                <div style="font-size: 2.2rem; margin-bottom: 6px;">📬</div>
                <span>Chưa có cuộc trò chuyện nào</span>
            </div>
        `;
        return;
    }

    let html = '';
    filtered.forEach(c => {
        const isActive = (currentCustomerId === c.customer_id);
        html += `
            <div class="conv-item ${isActive ? 'active' : ''}" onclick="selectConversation(${c.customer_id}, '${escapeJs(c.customer_name)}', '${escapeJs(c.customer_phone)}')">
                <div style="position: relative; width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, #d97706, #f59e0b); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800; flex-shrink: 0;">
                    ${c.avatar_char}
                    <span style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; border-radius: 50%; background: #10b981; border: 2px solid #ffffff;"></span>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 6px;">
                        <strong style="font-size: 0.88rem; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${c.customer_name}
                        </strong>
                        <span style="font-size: 0.7rem; color: #9ca3af; flex-shrink: 0;">${c.last_time}</span>
                    </div>
                    <div style="font-size: 0.78rem; color: ${isActive ? '#92400e' : '#6b7280'}; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        ${c.is_from_customer ? '👤 ' : 'Sạp: '}${c.last_message}
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function escapeJs(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function filterConversations() {
    const q = document.getElementById('convSearchInput')?.value || '';
    renderConversationsList(q);
}

// 2. Switch Room / Select Conversation
function switchSellerRoom(room) {
    currentRoom = room;
    const tabPrivate = document.getElementById('tabBtnPrivate');
    const tabPublic = document.getElementById('tabBtnPublic');

    if (room === 'private') {
        tabPrivate.style.background = '#d97706';
        tabPrivate.style.color = '#ffffff';
        tabPrivate.style.boxShadow = '0 2px 8px rgba(217, 119, 6, 0.3)';
        tabPublic.style.background = '#ffffff';
        tabPublic.style.color = '#78350f';
        tabPublic.style.boxShadow = 'none';

        if (allConversations.length > 0) {
            selectConversation(allConversations[0].customer_id, allConversations[0].customer_name, allConversations[0].customer_phone);
        } else {
            renderConversationsList();
        }
    } else {
        tabPublic.style.background = '#d97706';
        tabPublic.style.color = '#ffffff';
        tabPublic.style.boxShadow = '0 2px 8px rgba(217, 119, 6, 0.3)';
        tabPrivate.style.background = '#ffffff';
        tabPrivate.style.color = '#78350f';
        tabPrivate.style.boxShadow = 'none';

        currentCustomerId = null;
        renderConversationsList();

        document.getElementById('headerAvatar').innerHTML = '🏛️';
        document.getElementById('headerTitle').innerText = 'Phòng Chat Chung Chợ';
        document.getElementById('headerSubtitle').innerText = 'Trao đổi công khai với ban quản lý và cộng đồng đi chợ';
        document.getElementById('btnCallCustomer').style.display = 'none';

        fetchMessages();
    }
}

function selectConversation(customerId, name, phone) {
    currentCustomerId = customerId;
    currentCustomerName = name;
    currentCustomerPhone = phone;

    renderConversationsList();

    document.getElementById('headerAvatar').innerHTML = name ? name.slice(0, 1) : '👤';
    document.getElementById('headerTitle').innerText = name || 'Khách hàng';
    document.getElementById('headerSubtitle').innerText = phone ? `SĐT: ${phone} • Đang trò chuyện riêng tư` : 'Đang trò chuyện riêng tư';

    const callBtn = document.getElementById('btnCallCustomer');
    if (phone) {
        callBtn.style.display = 'inline-flex';
        callBtn.href = `tel:${phone}`;
    } else {
        callBtn.style.display = 'none';
    }

    lastMsgId = 0;
    fetchMessages();
}

// 3. Fetch & Render Messages Feed
async function fetchMessages() {
    const win = document.getElementById('sellerMessagesWindow');
    if (!win) return;

    const url = new URL('/seller/api/chat/messages', window.location.origin);
    url.searchParams.append('room', currentRoom);
    if (currentRoom === 'private' && currentCustomerId) {
        url.searchParams.append('customer_id', currentCustomerId);
    }

    try {
        const res = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();

        if (data.success) {
            const messages = data.messages;
            if (messages.length === 0) {
                win.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #9ca3af; text-align: center; padding: 20px;">
                        <span style="font-size: 2.5rem; margin-bottom: 8px;">💬</span>
                        <strong style="font-size: 0.95rem; color: #374151;">Chưa có tin nhắn nào</strong>
                        <p style="font-size: 0.8rem; margin: 4px 0 0 0;">Hãy gửi lời chào hoặc tư vấn món ngon cho khách hàng ngay bây giờ!</p>
                    </div>
                `;
                return;
            }

            let html = '';
            let lastDateGroup = '';
            let hasNewMsg = false;

            messages.forEach(msg => {
                if (msg.id > lastMsgId) hasNewMsg = true;

                if (msg.date_group !== lastDateGroup) {
                    html += `
                        <div style="text-align: center; margin: 12px 0 6px 0; position: relative;">
                            <span style="background: #e5e7eb; color: #4b5563; padding: 3px 12px; border-radius: 12px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase;">
                                ${msg.date_group}
                            </span>
                        </div>
                    `;
                    lastDateGroup = msg.date_group;
                }

                const isOwn = msg.is_own;

                let imageHtml = '';
                if (msg.image_url) {
                    imageHtml = `
                        <div style="margin-top: 6px; max-width: 220px; border-radius: 12px; overflow: hidden;">
                            <a href="${msg.image_url}" target="_blank">
                                <img src="${msg.image_url}" style="width: 100%; max-height: 160px; object-fit: cover; border-radius: 10px;">
                            </a>
                        </div>
                    `;
                }

                let productCardHtml = '';
                if (msg.product) {
                    productCardHtml = `
                        <div style="margin-top: 6px; background: rgba(0,0,0,0.06); padding: 8px 12px; border-radius: 12px; display: flex; gap: 8px; align-items: center; border: 1px solid rgba(0,0,0,0.08);">
                            <img src="${msg.product.image}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                            <div>
                                <div style="font-weight: 700; font-size: 0.82rem;">${msg.product.name}</div>
                                <div style="font-size: 0.78rem; font-weight: 800; color: ${isOwn ? '#fde68a' : '#d97706'};">${new Intl.NumberFormat('vi-VN').format(msg.product.price)}đ</div>
                            </div>
                        </div>
                    `;
                }

                html += `
                    <div class="chat-bubble-seller-row ${isOwn ? 'own' : 'other'}">
                        <div class="chat-bubble-box">
                            <div style="font-size: 0.72rem; font-weight: 700; opacity: 0.8; margin-bottom: 2px;">
                                ${msg.sender_name} • ${msg.time_formatted}
                            </div>
                            <div>${msg.message_text ? msg.message_text : ''}</div>
                            ${imageHtml}
                            ${productCardHtml}
                        </div>
                    </div>
                `;
            });

            win.innerHTML = html;
            if (messages.length > 0) {
                lastMsgId = messages[messages.length - 1].id;
            }

            if (hasNewMsg) {
                setTimeout(() => { win.scrollTop = win.scrollHeight; }, 50);
            }
        }
    } catch (err) {
        console.warn('Error fetching messages:', err);
    }
}

// 4. Send Message Handler
async function handleSellerChatSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('sellerMsgInput');
    const text = input ? input.value.trim() : '';
    const prodId = document.getElementById('sellerAttachedProductId')?.value || null;
    const fileInput = document.getElementById('sellerChatImageInput');
    const imageFile = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;

    if (!text && !prodId && !imageFile) return;

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('room', currentRoom);
    if (currentRoom === 'private' && currentCustomerId) {
        formData.append('customer_id', currentCustomerId);
    }
    if (text) formData.append('message_text', text);
    if (prodId) formData.append('product_id', prodId);
    if (imageFile) formData.append('image', imageFile);

    const sendBtn = document.getElementById('btnSendSellerMsg');
    if (sendBtn) sendBtn.disabled = true;

    try {
        const res = await fetch('/seller/api/chat/send', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            if (input) input.value = '';
            clearAttachedProduct();
            clearAttachedImage();
            fetchMessages();
            fetchConversations();
        } else {
            alert(data.message || 'Lỗi gửi tin nhắn.');
        }
    } catch (err) {
        console.error('Error sending message:', err);
    } finally {
        if (sendBtn) sendBtn.disabled = false;
        if (input) input.focus();
    }
}

function sendSellerQuickReply(text) {
    const input = document.getElementById('sellerMsgInput');
    if (input) {
        input.value = text;
        const form = document.getElementById('sellerChatForm');
        if (form) form.requestSubmit();
    }
}

// 5. Product & Image Attachments
function toggleProductAttachDropdown() {
    const dd = document.getElementById('sellerProductAttachDropdown');
    if (dd) dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
}

function selectStallProduct(id, name, price) {
    document.getElementById('sellerAttachedProductId').value = id;
    document.getElementById('sellerAttachedProdName').innerText = name;
    document.getElementById('sellerAttachedProdPrice').innerText = new Intl.NumberFormat('vi-VN').format(price) + 'đ';
    document.getElementById('sellerProductPreview').style.display = 'block';
    toggleProductAttachDropdown();
}

function clearAttachedProduct() {
    document.getElementById('sellerAttachedProductId').value = '';
    document.getElementById('sellerProductPreview').style.display = 'none';
}

function handleSellerImageSelected(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('sellerAttachedImgElem').src = e.target.result;
            document.getElementById('sellerImagePreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearAttachedImage() {
    const input = document.getElementById('sellerChatImageInput');
    if (input) input.value = '';
    document.getElementById('sellerImagePreview').style.display = 'none';
}

// 6. Init & Start Real-time Polling
document.addEventListener('DOMContentLoaded', function() {
    fetchConversations();
    chatPollTimer = setInterval(() => {
        fetchConversations();
        fetchMessages();
    }, 3000);
});

// Close dropdown on click outside
document.addEventListener('click', function(e) {
    const dd = document.getElementById('sellerProductAttachDropdown');
    const trigger = e.target.closest('[onclick="toggleProductAttachDropdown()"]');
    if (dd && dd.style.display === 'block' && !dd.contains(e.target) && !trigger) {
        dd.style.display = 'none';
    }
});
</script>
@endsection
