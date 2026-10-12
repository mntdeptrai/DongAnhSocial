<script>
    // Tab filtering logic
    const tabBtns = document.querySelectorAll('.tab-btn');
    const expCards = document.querySelectorAll('.exp-card-item');
    const searchInput = document.getElementById('exp-search-input');
    const noResultsBox = document.getElementById('no-results-box');

    let currentTab = 'all';
    let currentKeyword = '';

    function applyFilters() {
        let visibleCount = 0;
        expCards.forEach(card => {
            const kind = card.getAttribute('data-kind') || '';
            const tags = (card.getAttribute('data-tags') || '').toLowerCase();
            const title = (card.getAttribute('data-title') || '').toLowerCase();
            const desc = (card.getAttribute('data-desc') || '').toLowerCase();
            const loc = (card.getAttribute('data-location') || '').toLowerCase();

            // Tab check
            let tabMatch = false;
            if (currentTab === 'all') {
                tabMatch = true;
            } else if (currentTab === 'tour' && kind === 'tour') {
                tabMatch = true;
            } else if (currentTab === 'homestay' && (tags.includes('homestay') || title.includes('ăn tối'))) {
                tabMatch = true;
            } else if (currentTab === 'farming' && (tags.includes('farming') || title.includes('trồng rau'))) {
                tabMatch = true;
            } else if (currentTab === 'game' && (tags.includes('game') || title.includes('mật mã') || title.includes('bắn nỏ'))) {
                tabMatch = true;
            } else if (currentTab === 'craft' && (tags.includes('craft') || title.includes('bún') || title.includes('gốm'))) {
                tabMatch = true;
            } else if (currentTab === 'monument' && (tags.includes('monument') || tags.includes('service') || title.includes('tham quan'))) {
                tabMatch = true;
            }

            // Keyword check
            let searchMatch = true;
            if (currentKeyword) {
                const kw = currentKeyword.toLowerCase();
                searchMatch = title.includes(kw) || desc.includes(kw) || loc.includes(kw);
            }

            if (tabMatch && searchMatch) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResultsBox) {
            noResultsBox.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentTab = this.getAttribute('data-tab');
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentKeyword = this.value.trim();
            applyFilters();
        });
    }

    function resetAllFilters() {
        currentTab = 'all';
        currentKeyword = '';
        if (searchInput) searchInput.value = '';
        tabBtns.forEach(b => {
            if (b.getAttribute('data-tab') === 'all') b.classList.add('active');
            else b.classList.remove('active');
        });
        applyFilters();
    }

    // AI Matchmaker chip interaction
    function setupChipGroup(groupId) {
        const group = document.getElementById(groupId);
        if (!group) return;
        const chips = group.querySelectorAll('.ai-chip');
        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                chips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    setupChipGroup('ai-audience-group');
    setupChipGroup('ai-duration-group');
    setupChipGroup('ai-interest-group');

    // AI Button Action
    const btnRunAi = document.getElementById('btn-run-ai');
    const btnResetAi = document.getElementById('btn-reset-ai');
    const aiResultContainer = document.getElementById('ai-result-container');
    const aiResultTitle = document.getElementById('ai-result-title');
    const aiResultText = document.getElementById('ai-result-text');

    if (btnRunAi) {
        btnRunAi.addEventListener('click', function() {
            const audience = document.querySelector('#ai-audience-group .ai-chip.active')?.getAttribute('data-val') || 'all';
            const duration = document.querySelector('#ai-duration-group .ai-chip.active')?.getAttribute('data-val') || 'all';
            const interest = document.querySelector('#ai-interest-group .ai-chip.active')?.getAttribute('data-val') || 'all';

            this.innerHTML = '<span>⏳</span> Đang phân tích...';
            this.disabled = true;

            setTimeout(() => {
                this.innerHTML = '<span>✨</span> Gợi ý Lộ trình ngay';
                this.disabled = false;

                let title = 'Gợi ý từ Chuyên gia Đông Anh: ';
                let msg = '';
                let targetTab = 'all';

                if (interest === 'history') {
                    title += 'Game Mật Mã Loa Thành & Khám phá Di sản';
                    msg = 'Bạn nên thử ngay "Game Mật Mã Loa Thành" hoặc trải nghiệm bắn nỏ thần. Vừa vận động giải mật thư bí ẩn, vừa chiêm ngưỡng 3 vòng hào thành cổ kính!';
                    targetTab = 'game';
                } else if (interest === 'food') {
                    title += 'Trải nghiệm Ẩm thực Bản địa & Ăn tối Nhà dân Cổ Loa';
                    msg = 'Một trải nghiệm tuyệt vời vào buổi chiều tối: Ngồi giữa sân vườn nhà cổ, cùng gia chủ nấu cơm quê và thưởng thức mâm cỗ gà đồi, canh cua đồng ấm áp.';
                    targetTab = 'homestay';
                } else if (audience === 'family' || interest === 'craft') {
                    title += 'Tour Nông nghiệp Sinh thái Trồng rau tại Cổ Loa';
                    msg = 'Cực kỳ phù hợp cho cả gia đình và trẻ nhỏ: Mặc áo bà ba, xới đất ven bờ thành, gieo hạt, gánh nước tưới rau và tự tay thu hoạch giỏ rau sạch mang về nhà!';
                    targetTab = 'farming';
                } else {
                    title += 'Lộ trình Trải nghiệm Tổng hợp Văn hóa - Bản địa';
                    msg = 'Kết hợp tham quan di tích lịch sử Thành Cổ Loa với hoạt động làm bún Mạch Tràng hoặc ăn tối cùng người dân địa phương.';
                    targetTab = 'all';
                }

                if (aiResultContainer) {
                    aiResultTitle.innerText = title;
                    aiResultText.innerText = msg;
                    aiResultContainer.style.display = 'block';
                    aiResultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                const targetBtn = document.querySelector(`.tab-btn[data-tab="${targetTab}"]`);
                if (targetBtn) {
                    tabBtns.forEach(b => b.classList.remove('active'));
                    targetBtn.classList.add('active');
                    currentTab = targetTab;
                    applyFilters();
                }
            }, 300);
        });
    }

    if (btnResetAi) {
        btnResetAi.addEventListener('click', function() {
            document.querySelectorAll('.ai-chip').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('#ai-audience-group .ai-chip[data-val="all"], #ai-duration-group .ai-chip[data-val="all"], #ai-interest-group .ai-chip[data-val="all"]').forEach(c => c.classList.add('active'));
            if (aiResultContainer) aiResultContainer.style.display = 'none';
            resetAllFilters();
        });
    }

    // ==========================================
    // ACTIVITY DETAIL MODAL LOGIC
    // ==========================================
    let currentDetailData = null;

    function openActivityDetailModal(actJson) {
        try {
            const data = typeof actJson === 'string' ? JSON.parse(actJson) : actJson;
            currentDetailData = data;

            document.getElementById('detail-cover-img').src = data.image_path || 'https://media.xadonganh.com/experience/flycam_toan_canh_den_thuong_co_loa.jpg';
            document.getElementById('detail-title').innerText = data.name;
            document.getElementById('detail-location').innerText = data.location || (data.eatery?.name ?? 'Đông Anh, Hà Nội');
            
            document.getElementById('detail-badge-type').innerText = data.type_label || 'Trải nghiệm';
            document.getElementById('detail-badge-audience').innerText = data.audience || '⭐ Mọi lứa tuổi';
            
            document.getElementById('detail-price').innerText = data.price_formatted || (data.price > 0 ? Number(data.price).toLocaleString('vi-VN') + 'đ' : 'Theo yêu cầu');
            document.getElementById('detail-duration').innerText = data.duration || '2 - 3 giờ';
            document.getElementById('detail-best-time').innerText = data.best_time || 'Hằng ngày';
            document.getElementById('detail-description').innerText = data.description || '';

            // Render Highlights
            const highlightsList = document.getElementById('detail-highlights-list');
            highlightsList.innerHTML = '';
            const highlights = data.highlights || ['Trải nghiệm nhập vai thực tế cùng người bản xứ', 'Không gian văn hóa truyền thống thanh bình', 'Có sản phẩm/thành phẩm mang về làm kỷ niệm'];
            highlights.forEach(h => {
                const item = document.createElement('div');
                item.style.cssText = 'display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; color: var(--text-main); line-height: 1.5;';
                item.innerHTML = `<span style="color: #10b981; font-weight: 800;">✓</span> <span>${h}</span>`;
                highlightsList.appendChild(item);
            });

            // Render Timeline
            const timelineList = document.getElementById('detail-timeline-list');
            timelineList.innerHTML = '';
            const timeline = data.timeline || ['Chặng 1: Đón khách và làm quen với nghệ nhân.', 'Chặng 2: Trực tiếp tham gia hoạt động thực hành.', 'Chặng 3: Thưởng thức thành phẩm và chụp ảnh lưu niệm.'];
            timeline.forEach((step, idx) => {
                const item = document.createElement('div');
                item.style.cssText = 'font-size: 0.84rem; color: var(--text-muted); line-height: 1.5; position: relative;';
                item.innerHTML = `<strong style="color: #10b981;">• Bước ${idx + 1}:</strong> ${step.replace(/^Chặng \d+:\s*/, '')}`;
                timelineList.appendChild(item);
            });

            document.getElementById('detail-included').innerText = data.included || 'Chi phí trọn gói trải nghiệm.';
            document.getElementById('detail-notice').innerText = data.notice || 'Nên liên hệ đặt trước ít nhất 2 giờ.';

            const phone = (data.eatery?.phone || '02439652973').replace(/[^0-9]/g, '');
            const zaloMsg = encodeURIComponent(`Xin chào, tôi muốn tìm hiểu thêm về hoạt động: "${data.name}".`);
            document.getElementById('detail-zalo-link').href = `https://zalo.me/${phone}?text=${zaloMsg}`;

            document.getElementById('activityDetailModal').style.display = 'flex';
        } catch (e) {
            console.error('Error opening detail modal:', e);
        }
    }

    function closeActivityDetailModal() {
        const modal = document.getElementById('activityDetailModal');
        if (modal) modal.style.display = 'none';
    }

    function triggerBookFromDetail() {
        if (!currentDetailData) return;
        closeActivityDetailModal();
        openArtisanModal(
            currentDetailData.name,
            currentDetailData.location || (currentDetailData.eatery?.name ?? 'Đông Anh'),
            currentDetailData.eatery?.phone || '0243.965.2973',
            currentDetailData.type_label || 'Trải nghiệm'
        );
    }

    // ==========================================
    // ARTISAN BOOKING MODAL LOGIC
    // ==========================================
    const artisanModal = document.getElementById('artisanModal');
    const modalActName = document.getElementById('modal-act-name');
    const modalLocName = document.getElementById('modal-location-name');
    const modalTagBadge = document.getElementById('modal-tag-badge');
    const modalTelLink = document.getElementById('modal-tel-link');
    const modalZaloLink = document.getElementById('modal-zalo-link');

    function openArtisanModal(actName, locName, phone, badgeText) {
        if (!artisanModal) return;
        const cleanPhone = (phone || '02439652973').replace(/[^0-9]/g, '');
        
        modalActName.innerText = actName;
        modalLocName.innerText = '📍 ' + locName;
        modalTagBadge.innerText = badgeText || 'Trải nghiệm';
        modalTelLink.href = 'tel:' + cleanPhone;
        
        const zaloMsg = encodeURIComponent(`Xin chào, tôi muốn tìm hiểu và đăng ký tham gia hoạt động: "${actName}" tại ${locName}.`);
        modalZaloLink.href = 'https://zalo.me/' + cleanPhone + '?text=' + zaloMsg;

        artisanModal.style.display = 'flex';
    }

    function closeArtisanModal() {
        if (artisanModal) artisanModal.style.display = 'none';
    }

    function handleBookingSubmit(e) {
        e.preventDefault();
        const name = document.getElementById('book-name').value;
        const phone = document.getElementById('book-phone').value;
        const count = document.getElementById('book-count').value;
        const date = document.getElementById('book-date').value || 'Sớm nhất';

        closeArtisanModal();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '🎉 Tiếp nhận thành công!',
                html: `Cảm ơn <strong>${name}</strong>!<br>Yêu cầu trải nghiệm cho <strong>${count} người</strong> (ngày: <strong>${date}</strong>) đã được gửi đến Ban tổ chức.<br>Nghệ nhân sẽ liên hệ qua SĐT <strong>${phone}</strong> để xác nhận chi tiết.`,
                icon: 'success',
                confirmButtonText: 'Đã hiểu',
                confirmButtonColor: '#10b981'
            });
        } else {
            alert(`Cảm ơn ${name}! Yêu cầu trải nghiệm đã được tiếp nhận. Nghệ nhân sẽ liên hệ SĐT ${phone} để xác nhận.`);
        }
        document.getElementById('booking-form').reset();
    }

    // ==========================================
    // FEEDBACK & DIARY LOGIC (AIRBNB MODEL)
    // ==========================================
    let currentSpotlightFilter = 'all';

    function filterSpotlightFeedbacks(filterVal, btnElement) {
        currentSpotlightFilter = filterVal;
        
        // Update active class on pills
        const pills = document.querySelectorAll('#fb-filter-pills-row .fb-pill');
        pills.forEach(p => p.classList.remove('active'));
        if (btnElement) btnElement.classList.add('active');

        const cards = document.querySelectorAll('#feedback-grid-container .feedback-card');
        let count = 0;

        cards.forEach(card => {
            const act = card.getAttribute('data-activity') || '';
            const rating = card.getAttribute('data-rating') || '5';
            const hasPhoto = card.getAttribute('data-has-photo') === '1';

            let match = false;
            if (filterVal === 'all') {
                match = true;
            } else if (filterVal === 'has_photo') {
                match = hasPhoto;
            } else if (filterVal === '5star') {
                match = rating === '5';
            } else if (act.includes(filterVal)) {
                match = true;
            }

            if (match) {
                // When filtering by specific tag, reveal matching items
                if (filterVal !== 'all' || count < 6) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
                count++;
            } else {
                card.style.display = 'none';
            }
        });

        const badge = document.getElementById('fb-visible-count-badge');
        if (badge) badge.innerText = Math.min(count, 6);

        const loadMoreBtn = document.getElementById('btn-load-more-fb');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = (filterVal === 'all' && count > 6) ? 'inline-flex' : 'none';
        }
    }

    function loadMoreSpotlightFeedbacks() {
        const extraCards = document.querySelectorAll('#feedback-grid-container .fb-card-extra');
        extraCards.forEach(c => {
            c.style.display = 'flex';
            c.style.animation = 'fadeIn 0.4s ease';
        });

        const loadMoreBtn = document.getElementById('btn-load-more-fb');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = 'none';
        }

        const allCards = document.querySelectorAll('#feedback-grid-container .feedback-card');
        const badge = document.getElementById('fb-visible-count-badge');
        if (badge) badge.innerText = allCards.length;
    }

    // Helpful like counter toggle
    function toggleHelpful(btn, fbId) {
        const countSpan = btn.querySelector('.helpful-count');
        if (!countSpan) return;

        let current = parseInt(countSpan.innerText) || 0;
        const isLiked = btn.classList.contains('active');

        if (isLiked) {
            btn.classList.remove('active');
            countSpan.innerText = Math.max(0, current - 1);
        } else {
            btn.classList.add('active');
            countSpan.innerText = current + 1;

            // Micro-animation bounce
            btn.style.transform = 'scale(1.15)';
            setTimeout(() => { btn.style.transform = 'none'; }, 200);
        }
    }

    // Fullscreen Airbnb Review Library Drawer
    function openFeedbackLibraryModal(initialActivity = null) {
        const modal = document.getElementById('feedbackLibraryModal');
        if (!modal) return;

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        if (initialActivity) {
            const select = document.getElementById('lib-activity-select');
            if (select) {
                // Find matching option or set exact value
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value.includes(initialActivity) || initialActivity.includes(select.options[i].value)) {
                        select.selectedIndex = i;
                        break;
                    }
                }
            }
        }

        filterLibraryReviews();
    }

    function closeFeedbackLibraryModal() {
        const modal = document.getElementById('feedbackLibraryModal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    function filterLibraryReviews() {
        const searchInput = document.getElementById('lib-search-input');
        const kw = searchInput ? searchInput.value.trim().toLowerCase() : '';

        const actSelect = document.getElementById('lib-activity-select');
        const selectedAct = actSelect ? actSelect.value : 'all';

        const rateSelect = document.getElementById('lib-rating-select');
        const selectedRating = rateSelect ? rateSelect.value : 'all';

        const hasPhotoCb = document.getElementById('lib-has-photo-cb');
        const onlyHasPhoto = hasPhotoCb ? hasPhotoCb.checked : false;

        const items = document.querySelectorAll('#lib-feedback-list .lib-review-item');
        let visibleCount = 0;

        items.forEach(item => {
            const content = item.getAttribute('data-content') || '';
            const act = item.getAttribute('data-activity') || '';
            const rating = item.getAttribute('data-rating') || '5';
            const hasPhoto = item.getAttribute('data-has-photo') === '1';

            let matchKw = !kw || content.includes(kw);
            let matchAct = selectedAct === 'all' || act.includes(selectedAct) || selectedAct.includes(act);
            let matchRating = selectedRating === 'all' || rating === selectedRating;
            let matchPhoto = !onlyHasPhoto || hasPhoto;

            if (matchKw && matchAct && matchRating && matchPhoto) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const countSpan = document.getElementById('lib-visible-count');
        if (countSpan) countSpan.innerText = visibleCount;

        const emptyState = document.getElementById('lib-empty-state');
        if (emptyState) emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function resetLibraryFilters() {
        const searchInput = document.getElementById('lib-search-input');
        if (searchInput) searchInput.value = '';

        const actSelect = document.getElementById('lib-activity-select');
        if (actSelect) actSelect.value = 'all';

        const rateSelect = document.getElementById('lib-rating-select');
        if (rateSelect) rateSelect.value = 'all';

        const hasPhotoCb = document.getElementById('lib-has-photo-cb');
        if (hasPhotoCb) hasPhotoCb.checked = false;

        filterLibraryReviews();
    }

    // Modal submit feedback
    function openFeedbackModal() {
        const modal = document.getElementById('feedbackSubmitModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeFeedbackModal() {
        const modal = document.getElementById('feedbackSubmitModal');
        if (modal) modal.style.display = 'none';
    }

    async function handleFeedbackSubmit(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btn-submit-fb');
        const author = document.getElementById('fb-author').value.trim();
        const activity = document.getElementById('fb-activity').value;
        const rating = document.querySelector('input[name="fb-rating"]:checked')?.value || 5;
        const comment = document.getElementById('fb-comment').value.trim();
        const image = document.getElementById('fb-image').value.trim();

        if (!author || !comment) return;

        submitBtn.innerHTML = '⏳ Đang đăng cảm nhận...';
        submitBtn.disabled = true;

        try {
            const token = document.querySelector('input[name="_token"]')?.value;
            const res = await fetch('/exp-corner/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    author_name: author,
                    activity_name: activity,
                    rating: parseInt(rating),
                    comment: comment,
                    image_url: image
                })
            });

            const data = await res.json();
            closeFeedbackModal();
            document.getElementById('exp-feedback-form').reset();

            // Append newly posted feedback card to DOM spotlight grid
            const container = document.getElementById('feedback-grid-container');
            if (container) {
                const card = document.createElement('div');
                card.className = 'feedback-card';
                card.setAttribute('data-activity', activity);
                card.setAttribute('data-rating', rating);
                card.setAttribute('data-has-photo', image ? '1' : '0');
                card.style.cssText = 'background: var(--bg-card); border: 2px solid #10b981; border-radius: 20px; padding: 22px; display: flex; flex-direction: column; animation: fadeIn 0.5s ease; box-shadow: 0 8px 25px rgba(16,185,129,0.15);';
                
                let starsStr = '';
                for (let i = 0; i < rating; i++) starsStr += '⭐';

                card.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem; text-transform: uppercase;">
                                ${author.charAt(0)}
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <strong style="font-size: 0.92rem; color: var(--text-main);">${author}</strong>
                                    <span class="verified-badge">✓ Đã trải nghiệm</span>
                                </div>
                                <span style="font-size: 0.72rem; color: #10b981; font-weight: 700;">Vừa đăng xong</span>
                            </div>
                        </div>
                        <div style="color: #f59e0b; font-size: 0.8rem; font-weight: 800; background: #fffbeb; padding: 4px 8px; border-radius: 12px; border: 1px solid #fef3c7;">
                            ${starsStr}
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <span style="font-size: 0.74rem; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); padding: 4px 10px; border-radius: 20px; font-weight: 700; display: inline-block;">
                            🎯 ${activity}
                        </span>
                    </div>
                    <p style="margin: 0 0 14px 0; font-size: 0.86rem; color: var(--text-main); line-height: 1.6; font-style: italic; flex: 1;">
                        "${comment}"
                    </p>
                    ${image ? `<div style="position: relative; height: 160px; border-radius: 14px; overflow: hidden; border: 1px solid rgba(16, 185, 129, 0.2); margin-bottom: 12px;"><img src="${image}" style="width: 100%; height: 100%; object-fit: cover;" alt="Kỷ niệm"></div>` : ''}
                    <div style="display: flex; align-items: center; justify-content: flex-end; margin-top: auto;">
                        <button type="button" class="btn-helpful active" onclick="toggleHelpful(this, 'fb_new')">
                            <span>👍</span> Hữu ích (<span class="helpful-count">1</span>)
                        </button>
                    </div>
                `;
                container.prepend(card);
            }

            // Also prepend to library list
            const libList = document.getElementById('lib-feedback-list');
            if (libList) {
                const libArticle = document.createElement('article');
                libArticle.className = 'lib-review-item';
                libArticle.setAttribute('data-activity', activity);
                libArticle.setAttribute('data-rating', rating);
                libArticle.setAttribute('data-has-photo', image ? '1' : '0');
                libArticle.setAttribute('data-content', (author + ' ' + comment + ' ' + activity).toLowerCase());
                libArticle.style.cssText = 'padding: 22px; border-radius: 18px; background: var(--bg-card); border: 2px solid #10b981; animation: fadeIn 0.4s ease; margin-bottom: 20px;';

                let starsStr = '';
                for (let i = 0; i < rating; i++) starsStr += '⭐';

                libArticle.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; text-transform: uppercase;">
                                ${author.charAt(0)}
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <strong style="font-size: 0.96rem; color: var(--text-main);">${author}</strong>
                                    <span class="verified-badge">✓ Vừa chia sẻ</span>
                                </div>
                                <span style="font-size: 0.74rem; color: #10b981; font-weight: 700;">Vừa đăng xong</span>
                            </div>
                        </div>
                        <div style="color: #f59e0b; font-size: 0.82rem; font-weight: 800; background: #fffbeb; padding: 4px 10px; border-radius: 12px; border: 1px solid #fef3c7;">
                            ${starsStr}
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <span style="font-size: 0.74rem; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); padding: 4px 12px; border-radius: 20px; font-weight: 700; display: inline-block;">
                            🎯 ${activity}
                        </span>
                    </div>
                    <p style="margin: 0 0 14px 0; font-size: 0.88rem; color: var(--text-main); line-height: 1.65;">
                        "${comment}"
                    </p>
                    ${image ? `<div style="position: relative; max-width: 380px; height: 210px; border-radius: 14px; overflow: hidden; border: 1.5px solid rgba(16, 185, 129, 0.25); margin-bottom: 14px;"><img src="${image}" style="width: 100%; height: 100%; object-fit: cover;" alt="Ảnh kỷ niệm"></div>` : ''}
                    <div style="margin-top: 14px; display: flex; align-items: center; justify-content: flex-end;">
                        <button type="button" class="btn-helpful active" onclick="toggleHelpful(this, 'fb_new')">
                            <span>👍</span> Hữu ích (<span class="helpful-count">1</span>)
                        </button>
                    </div>
                `;
                libList.prepend(libArticle);
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '🎉 Xuất bản thành công!',
                    text: 'Cảm ơn bạn đã gửi cảm nhận! Nhật ký của bạn đã được hiển thị trên bảng tin cộng đồng và thư viện trải nghiệm.',
                    icon: 'success',
                    confirmButtonText: 'Tuyệt vời',
                    confirmButtonColor: '#10b981'
                });
            } else {
                alert('Xuất bản cảm nhận thành công!');
            }
        } catch (err) {
            console.error('Error submitting feedback:', err);
            alert('Có lỗi xảy ra khi lưu cảm nhận. Vui lòng thử lại sau.');
        } finally {
            submitBtn.innerHTML = '🚀 Xuất bản Cảm Nhận';
            submitBtn.disabled = false;
        }
    }

    // Community Diaries Modal
    function openDiariesModal(tourId) {
        const modal = document.getElementById('communityDiariesModal-' + tourId);
        if (modal) modal.style.display = 'flex';
    }
    function closeDiariesModal(tourId) {
        const modal = document.getElementById('communityDiariesModal-' + tourId);
        if (modal) modal.style.display = 'none';
    }

    // Close any modal when clicking outside
    window.addEventListener('click', function(e) {
        if (artisanModal && e.target === artisanModal) closeArtisanModal();
        const actModal = document.getElementById('activityDetailModal');
        if (actModal && e.target === actModal) closeActivityDetailModal();
        const fbModal = document.getElementById('feedbackSubmitModal');
        if (fbModal && e.target === fbModal) closeFeedbackModal();
        const libModal = document.getElementById('feedbackLibraryModal');
        if (libModal && e.target === libModal) closeFeedbackLibraryModal();
    });

    // Close on Escape key press
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeActivityDetailModal();
            closeArtisanModal();
            closeFeedbackModal();
            closeFeedbackLibraryModal();
        }
    });
</script>
