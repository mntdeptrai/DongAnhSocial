<script>
    let currentActiveProduct = null;
    let currentMamCoImage = 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg';
    let currentMamCoCaption = 'Bản thực đơn cổ truyền Cổ Loa – Nền trống đồng Đông Sơn';
    let currentModalActiveImg = '';
    let currentModalActiveCaption = '';

    /**
     * Switch media in Spotlight Box for Mâm Cỗ Cổ Loa Xưa
     */
    function switchMamCoMedia(tab) {
        const mainImg = document.getElementById('mam-co-main-img');
        const tagLbl = document.getElementById('mam-co-tag-lbl');
        const btnSodo = document.getElementById('tab-btn-sodo');
        const btnThucdon = document.getElementById('tab-btn-thucdon');

        if (tab === 'sodo') {
            currentMamCoImage = 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_so_do.png';
            currentMamCoCaption = 'Sơ đồ bài trí mâm cỗ Cổ Loa xưa trên mâm đồng tròn';
            if (mainImg) mainImg.src = currentMamCoImage;
            if (tagLbl) tagLbl.innerText = '🔘 Sơ đồ bài trí trên mâm đồng tròn';
            if (btnSodo) btnSodo.classList.add('active');
            if (btnThucdon) btnThucdon.classList.remove('active');
        } else {
            currentMamCoImage = 'https://media.xadonganh.com/experience/mam_co_co_loa_xua_thuc_don.jpg';
            currentMamCoCaption = 'Bản thực đơn cổ truyền Cổ Loa – Nền trống đồng Đông Sơn';
            if (mainImg) mainImg.src = currentMamCoImage;
            if (tagLbl) tagLbl.innerText = '📜 Bản thực đơn cổ truyền Cổ Loa';
            if (btnThucdon) btnThucdon.classList.add('active');
            if (btnSodo) btnSodo.classList.remove('active');
        }
    }

    /**
     * Open Lightbox Modal for Full Resolution Menu Images
     */
    function openMamCoLightbox(imgSrc, caption) {
        const lightbox = document.getElementById('mamCoLightbox');
        const img = document.getElementById('lightbox-img');
        const cap = document.getElementById('lightbox-caption');
        if (lightbox && img) {
            img.src = imgSrc || currentMamCoImage;
            if (cap) cap.innerText = caption || currentMamCoCaption;
            lightbox.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Close Lightbox Modal
     */
    function closeMamCoLightbox() {
        const lightbox = document.getElementById('mamCoLightbox');
        if (lightbox) {
            lightbox.style.display = 'none';
            const detailModal = document.getElementById('nsDetailModal');
            if (!detailModal || detailModal.style.display === 'none') {
                document.body.style.overflow = '';
            }
        }
    }

    /**
     * Switch media inside product detail modal
     * Tab 1: Bản thực đơn cổ (Scroll menu - Preferred by user)
     * Tab 2: Sơ đồ mâm đồng tròn (Diagram)
     */
    function switchModalMedia(tabIdx) {
        const coverImg = document.getElementById('modal-cover-img');
        const btn1 = document.getElementById('modal-thumb-btn-1');
        const btn2 = document.getElementById('modal-thumb-btn-2');
        if (!currentActiveProduct) return;

        if (tabIdx === 1) {
            currentModalActiveImg = currentActiveProduct.menu_image || currentActiveProduct.image;
            currentModalActiveCaption = currentActiveProduct.name + ' - Bản thực đơn cổ truyền Cổ Loa';
            if (coverImg) coverImg.src = currentModalActiveImg;
            if (btn1) btn1.classList.add('active');
            if (btn2) btn2.classList.remove('active');
        } else {
            currentModalActiveImg = currentActiveProduct.diagram_image || currentActiveProduct.image;
            currentModalActiveCaption = currentActiveProduct.name + ' - Sơ đồ bài trí mâm đồng tròn';
            if (coverImg) coverImg.src = currentModalActiveImg;
            if (btn2) btn2.classList.add('active');
            if (btn1) btn1.classList.remove('active');
        }
    }

    function handleModalCoverClick() {
        if (currentModalActiveImg) {
            openMamCoLightbox(currentModalActiveImg, currentModalActiveCaption);
        }
    }

    /**
     * Filter products by category pill
     */
    function filterProducts(category, btnElement) {
        document.querySelectorAll('.ns-filter-pill').forEach(btn => btn.classList.remove('active'));
        if (btnElement) {
            btnElement.classList.add('active');
        }

        const cards = document.querySelectorAll('.ns-product-card');
        const searchVal = (document.getElementById('nsSearchInput')?.value || '').toLowerCase().trim();

        let visibleCount = 0;
        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            const cardTitle = (card.getAttribute('data-name') || '').toLowerCase();
            const cardOrigin = (card.getAttribute('data-origin') || '').toLowerCase();
            const cardDesc = (card.getAttribute('data-summary') || '').toLowerCase();

            const matchesCategory = (category === 'all' || cardCat === category);
            const matchesSearch = !searchVal || cardTitle.includes(searchVal) || cardOrigin.includes(searchVal) || cardDesc.includes(searchVal);

            if (matchesCategory && matchesSearch) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle empty state
        const emptyState = document.getElementById('ns-empty-state');
        if (emptyState) {
            emptyState.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }

    /**
     * Search filter products with clear button toggle
     */
    function searchProducts() {
        const searchInput = document.getElementById('nsSearchInput');
        const clearBtn = document.getElementById('nsSearchClear');
        const searchVal = (searchInput?.value || '').trim();

        if (clearBtn) {
            clearBtn.style.display = searchVal.length > 0 ? 'inline-flex' : 'none';
        }

        const activePill = document.querySelector('.ns-filter-pill.active');
        const activeCat = activePill ? activePill.getAttribute('data-category') : 'all';
        filterProducts(activeCat, activePill);
    }

    /**
     * Clear search input and restore filter
     */
    function clearSearch() {
        const searchInput = document.getElementById('nsSearchInput');
        const clearBtn = document.getElementById('nsSearchClear');
        if (searchInput) searchInput.value = '';
        if (clearBtn) clearBtn.style.display = 'none';

        const activePill = document.querySelector('.ns-filter-pill.active');
        const activeCat = activePill ? activePill.getAttribute('data-category') : 'all';
        filterProducts(activeCat, activePill);
    }

    /**
     * Clear search and reset to show all products with smooth scroll
     */
    function clearSearchAndShowAll() {
        clearSearch();
        const allPill = document.querySelector('.ns-filter-pill[data-category="all"]');
        filterProducts('all', allPill);
        const catalogElem = document.getElementById('ns-catalog-anchor');
        if (catalogElem) {
            catalogElem.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /**
     * Open product detail modal
     */
    function openNsDetailModal(productJson) {
        try {
            const product = typeof productJson === 'string' ? JSON.parse(productJson) : productJson;
            currentActiveProduct = product;

            // Cover image
            currentModalActiveImg = product.image;
            currentModalActiveCaption = product.name;
            const coverImg = document.getElementById('modal-cover-img');
            if (coverImg) {
                coverImg.src = product.image;
                coverImg.alt = product.name;
            }

            // Image gallery strip in modal
            const galleryStrip = document.getElementById('modal-gallery-strip');
            if (galleryStrip) {
                if (product.menu_image) {
                    galleryStrip.style.display = 'flex';
                    switchModalMedia(1);
                } else {
                    galleryStrip.style.display = 'none';
                }
            }

            document.getElementById('modal-badge').innerText = product.badge || 'Đặc sản';
            document.getElementById('modal-title').innerText = product.name;
            document.getElementById('modal-origin').innerText = product.origin;
            document.getElementById('modal-price').innerText = product.price_label || 'Liên hệ';
            document.getElementById('modal-unit').innerText = product.unit || 'Theo quy cách';
            document.getElementById('modal-story').innerText = product.story || product.summary;

            const tipElem = document.getElementById('modal-experience-tip');
            if (tipElem) {
                tipElem.innerText = product.experience_tip ? ('💡 ' + product.experience_tip) : '';
            }

            // Menu sections breakdown in modal (if present)
            const menuSectionsWrap = document.getElementById('modal-menu-sections-wrap');
            const menuSectionsBody = document.getElementById('modal-menu-sections-body');
            const highlightsWrap = document.getElementById('modal-highlights-wrap');
            if (menuSectionsWrap && menuSectionsBody) {
                if (product.menu_sections && typeof product.menu_sections === 'object') {
                    menuSectionsWrap.style.display = 'block';
                    if (highlightsWrap) highlightsWrap.style.display = 'none';

                    const iconMap = {
                        'I. MÓN MẶN': '🥩',
                        'II. BÁNH & XÔI': '🍚',
                        'III. RAU & CANH': '🥗',
                        'IV. TRÁNG MIỆNG & TRÀ': '🍵',
                        'Món mặn': '🥩',
                        'Bánh & Xôi': '🍚',
                        'Rau & Canh': '🥗',
                        'Tráng miệng & Trà': '🍵'
                    };

                    let html = `
                    <div class="ns-mamco-royal-parchment">
                        <div class="ns-parchment-inner-border">
                            <div class="ns-parchment-header">
                                <span class="ns-parchment-badge">✦ DI SẢN ẨM THỰC ĐẤT CỐ ĐÔ ✦</span>
                                <h4 class="ns-parchment-title">CỔ LOA - MÂM CỖ XƯA</h4>
                                <p class="ns-parchment-subtitle">“Hương vị từ đất cổ – Nếp quê còn mãi”</p>
                                <div class="ns-parchment-divider">
                                    <span>✦</span><hr><span>❖</span><hr><span>✦</span>
                                </div>
                            </div>
                            <div class="ns-parchment-grid">
                    `;

                    for (const [secTitle, items] of Object.entries(product.menu_sections)) {
                        const icon = iconMap[secTitle] || '🥢';
                        html += `
                            <div class="ns-parchment-sec-box">
                                <div class="ns-parchment-sec-head"><span>${icon}</span> ${secTitle}</div>
                                <ul class="ns-parchment-sec-items">
                                    ${items.map(it => `<li>${it}</li>`).join('')}
                                </ul>
                            </div>
                        `;
                    }

                    html += `
                            </div>
                            <div class="ns-parchment-footer-quote">
                                <p>“Một mâm cỗ – Một miền ký ức – Một Cổ Loa nghìn năm văn hiến.”</p>
                            </div>
                        </div>
                    </div>
                    `;

                    menuSectionsBody.innerHTML = html;
                } else {
                    menuSectionsWrap.style.display = 'none';
                    if (highlightsWrap) highlightsWrap.style.display = 'block';
                }
            }

            // Form hidden field
            document.getElementById('form-product-name').value = product.name;

            // Highlights
            const highlightsContainer = document.getElementById('modal-highlights');
            highlightsContainer.innerHTML = '';
            if (Array.isArray(product.highlights)) {
                product.highlights.forEach(h => {
                    const li = document.createElement('li');
                    li.innerText = h;
                    highlightsContainer.appendChild(li);
                });
            }

            // Direct call & Zalo
            const phone = product.contact_phone || '0243.965.2973';
            const cleanPhone = phone.replace(/[^0-9]/g, '');
            const callLink = document.getElementById('modal-call-link');
            if (callLink) {
                callLink.href = 'tel:' + cleanPhone;
                callLink.innerHTML = `<span>📞</span> Gọi hotline (${phone})`;
            }

            const zaloLink = document.getElementById('modal-zalo-link');
            if (zaloLink) {
                const zaloNumber = product.zalo || cleanPhone;
                zaloLink.href = 'https://zalo.me/' + zaloNumber;
            }

            // Show modal
            const modal = document.getElementById('nsDetailModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        } catch (e) {
            console.error('Error opening detail modal:', e);
        }
    }

    /**
     * Close product detail modal
     */
    function closeNsDetailModal() {
        const modal = document.getElementById('nsDetailModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function handleModalBackdropClick(e) {
        if (e.target.id === 'nsDetailModal') {
            closeNsDetailModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMamCoLightbox();
            closeNsDetailModal();
        }
    });

    /**
     * Submit inquiry / order form
     */
    async function handleInquirySubmit(e) {
        e.preventDefault();
        const form = document.getElementById('ns-inquiry-form');
        const submitBtn = document.getElementById('btn-submit-inquiry');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span>⏳</span> Đang gửi yêu cầu...`;

        const formData = new FormData(form);
        const payload = {
            product_name: formData.get('product_name'),
            customer_name: formData.get('customer_name'),
            phone: formData.get('phone'),
            quantity: formData.get('quantity'),
            note: formData.get('note'),
        };

        try {
            const response = await fetch('/nong-san-dong-anh/order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                closeNsDetailModal();
                form.reset();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '🎉 Tiếp nhận thành công!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'Đã hiểu',
                        confirmButtonColor: '#10b981',
                        customClass: {
                            popup: 'rounded-3xl shadow-2xl p-6'
                        }
                    });
                } else {
                    alert(data.message);
                }
            } else {
                alert('Có lỗi xảy ra, vui lòng thử lại hoặc gọi hotline trực tiếp!');
            }
        } catch (error) {
            console.error('Inquiry error:', error);
            alert('Không thể kết nối đến máy chủ. Vui lòng liên hệ trực tiếp qua hotline!');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
</script>
