<script>
    // 1. Khởi tạo dữ liệu JSON của các quán ăn được truyền từ PHP Controller
    const eateries = @json($eateries);
    let map;
    let markers = {};

    // 0. Motion One Animations Initialization
    document.addEventListener("DOMContentLoaded", () => {
        if (window.Motion) {
            const { animate } = window.Motion;

            // Animate each polaroid to its target rotation & load state dynamically
            const cards = document.querySelectorAll(".polaroid-card");
            cards.forEach((card, index) => {
                const angle = parseFloat(card.getAttribute("data-angle") || "0");
                animate(card,
                    { opacity: [0, 1], y: [40, 0], scale: [0.9, 1], rotate: [0, angle] },
                    {
                        delay: 0.15 + (index * 0.12),
                        duration: 0.8,
                        easing: "ease-out"
                    }
                );
            });

            // Animate food plate
            animate(".food-plate-container",
                { opacity: [0, 1], scale: [0.6, 1], rotate: [0, 10] },
                { delay: 0.7, duration: 0.85, easing: "ease-out" }
            );

            // Animate tent decoration
            animate(".tent-camp-container",
                { opacity: [0, 1], scale: [0.8, 1] },
                { delay: 0.55, duration: 0.8, easing: "ease-out" }
            );

            // Animate stamps/badges
            const stamps = document.querySelectorAll(".travel-stamp");
            stamps.forEach((stamp, index) => {
                animate(stamp,
                    { opacity: [0, 1], scale: [0, 1.2, 1] },
                    { delay: 0.95 + (index * 0.15), duration: 0.6, easing: "ease-out" }
                );
            });

            // Infinite subtle bobbing animation for brand logo and slogan
            animate(".logo-title",
                { y: [-5, 5] },
                { duration: 2.5, repeat: Infinity, direction: "alternate", easing: "ease-in-out" }
            );
            animate(".slogan-group",
                { rotate: [-1, 1], y: [1, -1] },
                { duration: 2.0, repeat: Infinity, direction: "alternate", easing: "ease-in-out" }
            );
        }
    });

    window.triggerQuickFilter = function(slug, href) {
        const categoryCard = document.querySelector(`.category-card[href*="cat=${slug}"]`);
        if (categoryCard) {
            document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
            categoryCard.classList.add('active');
            centerActiveCategoryCard(categoryCard);
            if (window.filterCategoryAjax) {
                window.filterCategoryAjax(slug, href);
            }
            setTimeout(() => {
                const splitList = document.querySelector('.split-list');
                if (splitList) {
                    window.scrollTo({
                        top: splitList.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }, 300);
        } else {
            window.location.href = href;
        }
    };

    // Hàm tự động cuộn thẻ danh mục được chọn vào chính giữa thanh trượt trơn tru (Google Maps/Airbnb Style)
    function centerActiveCategoryCard(cardElement) {
        const slider = document.querySelector('.categories-slider') || document.querySelector('.categories-container-wrap');
        if (!slider || !cardElement) return;

        const sliderWidth = slider.clientWidth;
        const sliderRect = slider.getBoundingClientRect();
        const cardRect = cardElement.getBoundingClientRect();
        
        // Vị trí thực tế của thẻ so với điểm bắt đầu của nội dung trượt (kể cả khi đã cuộn)
        const relativeLeft = cardRect.left - sliderRect.left + slider.scrollLeft;
        const cardWidth = cardElement.clientWidth;

        // Tính toán khoảng cách để đưa thẻ về giữa
        const targetScrollLeft = relativeLeft - (sliderWidth / 2) + (cardWidth / 2);

        slider.scrollTo({
            left: Math.max(0, targetScrollLeft),
            behavior: 'smooth'
        });
    }

    window.getSmartBusinessImgJS = function(name, desc) {
        const text = ((name || '') + ' ' + (desc || '')).toLowerCase();
        if (/thuốc|y tế|phòng khám|bác sĩ|nha khoa|pharmacy|clinic|medical|dược/i.test(text)) {
            return 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=600&q=80';
        }
        if (/spa|cắt tóc|làm đầu|gội đầu|nail|beauty|salon|barber|massage|thẩm mỹ/i.test(text)) {
            return 'https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=600&q=80';
        }
        if (/cơ khí|sửa chữa|ô tô|mô tô|xe máy|phụ tùng|kim loại|hàn|nhôm kính|đúc|sắt/i.test(text)) {
            return 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=600&q=80';
        }
        if (/may mặc|quần áo|thời trang|giày|dép|vải|boutique|clothing|fashion/i.test(text)) {
            return 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80';
        }
        if (/điện tử|máy tính|điện thoại|laptop|mobile|viễn thông|điện máy|điện gia dụng|camera/i.test(text)) {
            return 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=600&q=80';
        }
        if (/cà phê|cafe|coffee|trà|đồ uống|bánh|bakery|quán ăn|ẩm thực|nhà hàng|bún|phở|cơm|lẩu|nướng/i.test(text)) {
            return 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=600&q=80';
        }
        if (/xây dựng|vật liệu|xi măng|gạch|sơn|nội thất|gỗ|kính/i.test(text)) {
            return 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=600&q=80';
        }
        if (/nhà đất|bất động sản|cho thuê|quản lý nhà|mặt bằng|văn phòng|land|real estate/i.test(text)) {
            return 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=600&q=80';
        }
        if (/công ty|tnhh|cổ phần|doanh nghiệp|tập đoàn|enterprise/i.test(text)) {
            return 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&q=80';
        }
        return 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80';
    };

    document.addEventListener("DOMContentLoaded", function() {
        // 1. Kích hoạt cuộn bằng kéo chuột (Drag to scroll) cho máy tính & hỗ trợ vuốt chạm trên điện thoại
        const slider = document.querySelector('.categories-slider');
        if (slider) {
            let isDown = false;
            let startX;
            let scrollLeft;

            // Hỗ trợ cuộn ngang bằng lăn con lăn chuột
            slider.addEventListener('wheel', (e) => {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    slider.scrollLeft += e.deltaY * 0.9;
                }
            }, { passive: false });

            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                slider.style.cursor = 'grabbing';
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });
            slider.addEventListener('mouseleave', () => {
                isDown = false;
                slider.style.cursor = 'grab';
            });
            slider.addEventListener('mouseup', () => {
                isDown = false;
                slider.style.cursor = 'grab';
            });
            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 1.8;
                slider.scrollLeft = scrollLeft - walk;
            });
        }
        // 2. Thiết lập bản đồ Leaflet tâm vị trí Đông Anh (xã Đông Anh)
        map = L.map('map', {
            zoomControl: false, // Chúng ta sẽ tùy chỉnh vị trí nút zoom
            zoomSnap: 0.5,       // Bước zoom 0.5 giúp phản hồi nhanh nhạy
            zoomDelta: 0.5,      // Độ nhảy zoom mỗi lần cuộn
            wheelPxPerZoomLevel: 60, // Tốc độ zoom tiêu chuẩn nhanh & mượt
            zoomAnimation: true,
            fadeAnimation: true,
            markerZoomAnimation: true
        }).setView([21.1352, 105.8458], 13);
        
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // 3. Sử dụng Google Maps Tileset cho bản đồ
        let tileUrl = 'https://mt1.google.com/vt/lyrs=m&hl=vi&x={x}&y={y}&z={z}';
            
        let activeTileLayer = L.tileLayer(tileUrl, {
            attribution: '&copy; Google Maps',
            maxZoom: 20
        }).addTo(map);

        // Lắng nghe sự kiện đổi chế độ Sáng/Tối để giữ ổn định lớp nền bản đồ
        document.addEventListener('theme-changed', function(e) {
            map.removeLayer(activeTileLayer);
            activeTileLayer = L.tileLayer(tileUrl, {
                attribution: '&copy; Google Maps',
                maxZoom: 20
            }).addTo(map);
        });

        // 4. Định nghĩa hàm vẽ các địa điểm lên Bản đồ (Hỗ trợ gọi lại khi lọc AJAX và cuộn vô tận)
        function createSingleMarker(eat) {
            if (!eat.latitude || !eat.longitude || markers[eat.slug]) return;

            let categoryColor = '#0ea5e9'; // Mặc định xanh đại dương
            const catSlug = eat.category ? (eat.category.slug || eat.category) : '';
            
            if (catSlug === 'dong-anh-food-map') categoryColor = '#ff3366';
            else if (catSlug === 'stay-in-dong-anh') categoryColor = '#9d4edd';
            else if (catSlug === 'wellness-care') categoryColor = '#20b2aa';
            else if (catSlug === 'dong-anh-market') categoryColor = '#38b000';
            else if (catSlug === 'smart-education-map') categoryColor = '#4361ee';
            else if (catSlug === 'hanh-trinh-di-san') categoryColor = '#e63946';

            const catIcon = eat.category ? (eat.category.icon || '📍') : '📍';
            const catName = eat.category ? (eat.category.name || '') : '';
            const communeName = eat.commune ? (eat.commune.name || eat.commune) : '';
            const ratingVal = eat.average_rating || (eat.rating ? parseFloat(eat.rating).toFixed(1) : '5.0');

            const customIcon = L.divIcon({
                html: `<div style="background-color: ${categoryColor}; width: 28px; height: 28px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">${catIcon}</div>`,
                className: 'custom-leaflet-marker',
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            const signatureDishName = catSlug === 'dong-anh-food-map' ? 'Món ngon khám phá Đông Anh' : 
                                     (catSlug === 'dong-anh-market' ? 'Đặc sản OCOP vùng cố đô' : 
                                     (catSlug === 'wellness-care' ? 'Dịch vụ chăm sóc sức khỏe' : 
                                     (catSlug === 'smart-education-map' ? 'Học tập & Giáo dục thông minh' : 'Địa điểm nổi bật')));

            // Nội dung popup hiển thị nhanh
            const approvedVideos = eat.review_videos || eat.reviewVideos || [];
            const hasVideo = approvedVideos.length > 0;
            const videoBtn = hasVideo 
                ? `<button onclick="openReelsModal('${eat.slug}', '${eat.name.replace(/'/g, "\\'")}', '${signatureDishName}', '${eat.image_path}')" class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-heading); background: rgba(var(--primary-rgb), 0.08); border-color: rgba(var(--primary-rgb), 0.25); color: var(--primary); display: inline-flex; align-items: center; gap: 4px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(var(--primary-rgb), 0.15)'" onmouseout="this.style.background='rgba(var(--primary-rgb), 0.08)'">🎬 Video</button>`
                : '';

            const isEduMarker = (catSlug === 'smart-education-map' || (window.STORYTELLING_SCHOOLS && window.STORYTELLING_SCHOOLS[eat.slug]));
            const isOcopMarker = (catSlug === 'dong-anh-market' || (window.STORYTELLING_OCOP && window.STORYTELLING_OCOP[eat.slug]));
            
            const storyBtn = isEduMarker 
                ? `<button onclick="event.stopPropagation(); window.openSchoolStoryteller('${eat.slug}', '/dia-diem/${eat.slug}'); return false;" class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-heading); background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.3); color: #4f46e5; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; transition: all 0.2s; font-weight: 700;" onmouseover="this.style.background='rgba(99, 102, 241, 0.2)'" onmouseout="this.style.background='rgba(99, 102, 241, 0.12)'">📖 Story</button>` 
                : (isOcopMarker ? `<button onclick="event.stopPropagation(); window.openOcopStoryteller('${eat.slug}'); return false;" class="btn-secondary" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-heading); background: rgba(217, 119, 6, 0.15); border: 1px solid rgba(251, 191, 36, 0.4); color: #d97706; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; transition: all 0.2s; font-weight: 700;" onmouseover="this.style.background='rgba(217, 119, 6, 0.25)'" onmouseout="this.style.background='rgba(217, 119, 6, 0.15)'">🌾 Story</button>` : '');

            let markerImg = eat.image_path;
            if (!markerImg) {
                if (catSlug === 'co-so-kinh-doanh') {
                    markerImg = window.getSmartBusinessImgJS(eat.name, eat.description);
                } else {
                    markerImg = 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80';
                }
            }

            const popupContent = `
                <div class="map-popup-card">
                    <img src="${markerImg}" class="map-popup-img">
                    <h4 class="map-popup-title">${eat.name}</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 2px 0;">📍 ${communeName}</p>
                    <div class="map-popup-footer">
                        <span class="rating-stars">⭐ ${ratingVal}</span>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            ${videoBtn}
                            ${storyBtn}
                            ${catSlug === 'dong-anh-market' ? `<button onclick="event.stopPropagation(); window.openHomeOcopModalFromPopup('${eat.slug}', '${eat.name.replace(/'/g, "\\'")}')" class="btn-primary" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-heading); background: linear-gradient(135deg, #059669, #10b981); border: none; color: #ffffff; cursor: pointer;">Xem chi tiết</button>` : `<a href="/dia-diem/${eat.slug}" class="btn-primary" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-heading);">Xem chi tiết</a>`}
                        </div>
                    </div>
                </div>
            `;

            const marker = L.marker([eat.latitude, eat.longitude], { icon: customIcon })
                .bindPopup(popupContent);
                
            markers[eat.slug] = marker;
            return marker;
        }

        // MarkerCluster: gom nhóm markers để tránh tạo 1200+ DOM elements cùng lúc
        let markerClusterGroup = (typeof L.markerClusterGroup === 'function')
            ? L.markerClusterGroup({ maxClusterRadius: 50, chunkedLoading: true, chunkInterval: 100, chunkDelay: 20 })
            : null;
        if (markerClusterGroup) {
            map.addLayer(markerClusterGroup);
        }

        window.renderEateryMarkers = function(eateriesList) {
            // Xóa toàn bộ markers cũ trên bản đồ
            if (markerClusterGroup) {
                markerClusterGroup.clearLayers();
            } else {
                Object.values(markers).forEach(marker => {
                    map.removeLayer(marker);
                });
            }
            markers = {};

            if (Array.isArray(eateriesList)) {
                const newMarkers = [];
                eateriesList.forEach(function(eat) {
                    const m = createSingleMarker(eat);
                    if (m) {
                        if (markerClusterGroup) {
                            newMarkers.push(m);
                        } else {
                            m.addTo(map);
                        }
                    }
                });
                if (markerClusterGroup && newMarkers.length > 0) {
                    markerClusterGroup.addLayers(newMarkers);
                }
            }
        };

        window.appendEateryMarkers = function(eateriesList) {
            if (Array.isArray(eateriesList)) {
                const newMarkers = [];
                eateriesList.forEach(function(eat) {
                    const m = createSingleMarker(eat);
                    if (m) {
                        if (markerClusterGroup) {
                            newMarkers.push(m);
                        } else {
                            m.addTo(map);
                        }
                    }
                });
                if (markerClusterGroup && newMarkers.length > 0) {
                    markerClusterGroup.addLayers(newMarkers);
                }
            }
        };

        // Vẽ danh sách quán ăn ban đầu lên Bản đồ
        window.renderEateryMarkers(eateries);

        // 5. Logic tìm kiếm gõ real-time cực nhạy + Autocomplete gợi ý
        const searchInput = document.getElementById("searchInput");
        const suggestionDropdown = document.getElementById("suggestionDropdown");
        const cards = document.querySelectorAll('.split-list .eatery-card');
        const countSpan = document.getElementById("resultsCountSpan");
        
        // Hàm chuyển đổi tiếng Việt có dấu thành không dấu chính xác 100%
        const removeVietnameseTones = (str) => {
            if (!str) return '';
            str = str.toLowerCase();
            str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
            str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
            str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
            str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
            str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
            str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
            str = str.replace(/đ/g, "d");
            return str;
        };

        function filterEateries(query) {
            query = query.trim();
            const queryClean = removeVietnameseTones(query);
            const queryWords = queryClean.split(/\s+/).filter(w => w.length > 0);
            let matchCount = 0;

            const currentCards = document.querySelectorAll('.split-list .eatery-card, #eateriesListContainer .eatery-card, .eatery-card-item');

            currentCards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const address = card.getAttribute('data-address') || '';
                const desc = card.getAttribute('data-desc') || '';
                const commune = card.getAttribute('data-commune') || '';
                const taxCode = card.getAttribute('data-taxcode') || '';
                const owner = card.getAttribute('data-owner') || '';
                const phone = card.getAttribute('data-phone') || '';
                const slug = card.getAttribute('data-slug') || '';

                const rawText = `${name} ${address} ${desc} ${commune} ${taxCode} ${owner} ${phone}`;
                const textClean = removeVietnameseTones(rawText);

                // Mọi từ trong câu truy vấn tìm kiếm phải trùng khớp hoàn toàn (ALL words match)
                const isMatch = (queryWords.length === 0) || queryWords.every(word => textClean.includes(word));

                if (isMatch) {
                    card.style.setProperty('display', 'flex', 'important');
                    matchCount++;
                    if (markers[slug]) {
                        markers[slug].addTo(map);
                    }
                } else {
                    card.style.setProperty('display', 'none', 'important');
                    if (markers[slug]) {
                        map.removeLayer(markers[slug]);
                    }
                }
            });

            // Cập nhật số lượng kết quả hiển thị
            if (countSpan) {
                countSpan.innerText = `📍 (${matchCount} địa điểm phù hợp)`;
            }

            // Hiển thị hoặc ẩn phần thông báo không tìm thấy kết quả
            let noResultDiv = document.getElementById('noResultsPlaceholder');
            if (matchCount === 0 && queryWords.length > 0) {
                if (!noResultDiv) {
                    noResultDiv = document.createElement('div');
                    noResultDiv.id = 'noResultsPlaceholder';
                    noResultDiv.className = 'glass-panel';
                    noResultDiv.style.padding = '40px 20px';
                    noResultDiv.style.textAlign = 'center';
                    noResultDiv.style.color = 'var(--text-muted)';
                    noResultDiv.style.width = '100%';
                    noResultDiv.innerHTML = `
                        <p style="font-size: 1.2rem; margin-bottom: 8px; color: var(--text-main); font-weight: 800;">😔 Không tìm thấy kết quả phù hợp cho "${query}"</p>
                        <p style="font-size: 0.9rem;">Hãy kiểm tra lại từ khóa hoặc bấm nút bên dưới để xem toàn bộ danh sách!</p>
                        <button onclick="clearInlineSearch()" class="btn-primary" style="margin-top: 16px; padding: 10px 24px; cursor: pointer; border-radius: 12px; font-weight: 700;">Xem tất cả địa điểm</button>
                    `;
                    const container = document.querySelector('.split-list') || document.getElementById('eateriesListContainer');
                    if (container) container.appendChild(noResultDiv);
                } else {
                    noResultDiv.style.display = 'block';
                    const pTag = noResultDiv.querySelector('p');
                    if (pTag) pTag.innerText = `😔 Không tìm thấy kết quả phù hợp cho "${query}"`;
                }
            } else {
                if (noResultDiv) {
                    noResultDiv.style.display = 'none';
                }
            }
        }

        window.clearSearch = function() {
            searchInput.value = '';
            suggestionDropdown.style.display = 'none';
            filterEateries('');
        };

        // Xử lý lọc real-time trên thanh tìm kiếm trực tiếp ở khu vực danh mục
        const inlineSearchInput = document.getElementById('inlineSectionSearchInput');
        const inlineClearBtn = document.getElementById('inlineSearchClearBtn');

        if (inlineSearchInput) {
            inlineSearchInput.addEventListener('input', function() {
                const query = this.value;
                if (inlineClearBtn) {
                    inlineClearBtn.style.display = query.trim().length > 0 ? 'inline-flex' : 'none';
                }
                const mainSearchInput = document.getElementById('searchInput');
                if (mainSearchInput) mainSearchInput.value = query;
                
                filterEateries(query);
            });
        }

        window.clearInlineSearch = function() {
            if (inlineSearchInput) {
                inlineSearchInput.value = '';
                if (inlineClearBtn) inlineClearBtn.style.display = 'none';
            }
            const mainSearchInput = document.getElementById('searchInput');
            if (mainSearchInput) mainSearchInput.value = '';
            
            filterEateries('');
        };

        window.handleInlineSearchSubmit = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const query = inlineSearchInput ? inlineSearchInput.value.trim() : '';
            filterEateries(query);
            return false;
        };

        // Lọc real-time khi đang gõ trên ô tìm kiếm chính
        searchInput.addEventListener("input", function() {
            const query = this.value;
            filterEateries(query);
            
            if (query.trim().length < 2) {
                suggestionDropdown.style.display = "none";
                return;
            }

            fetch(`/tim-kiem?q=${encodeURIComponent(query)}&ajax=suggest`)
                .then(res => res.json())
                .then(data => {
                    suggestionDropdown.innerHTML = "";
                    if (data.length === 0) {
                        suggestionDropdown.style.display = "none";
                        return;
                    }

                    data.forEach(item => {
                        const div = document.createElement("div");
                        div.className = "suggestion-item";
                        div.innerHTML = `
                            <div>
                                <div class="suggestion-title">${item.name}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">📍 ${item.address}</div>
                            </div>
                            <span class="suggestion-type">Xem chi tiết ➔</span>
                        `;
                        div.onclick = function() {
                            window.location.href = `/dia-diem/${item.slug}`;
                        };
                        suggestionDropdown.appendChild(div);
                    });

                    suggestionDropdown.style.display = "block";
                });
        });

        // Chặn reload trang khi submit Form tìm kiếm trang chủ, thực hiện lọc ngay tại chỗ cực nhạy
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                filterEateries(searchInput.value);
                suggestionDropdown.style.display = "none";
            });
        }

        // Đóng dropdown khi click ngoài
        document.addEventListener("click", function(e) {
            if (e.target !== searchInput && e.target !== suggestionDropdown) {
                suggestionDropdown.style.display = "none";
            }
        });

        // 9. Lắng nghe click danh mục để lọc AJAX (Không reload trang, mượt mà kiểu SPA)
        const catCards = document.querySelectorAll('.category-card');
        catCards.forEach(card => {
            card.addEventListener('click', function(e) {
                const hrefAttr = this.getAttribute('href') || '';
                if (this.getAttribute('target') === '_blank' || hrefAttr === '/checkin' || hrefAttr.startsWith('/tuyen-duong')) {
                    return; // Let the link navigate normally!
                }
                e.preventDefault();
                
                const href = this.getAttribute('href');
                const urlParams = new URLSearchParams(href.split('?')[1] || '');
                const slug = urlParams.get('cat') || '';
                
                // Đánh dấu nút đang chọn
                catCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                // Tự động cuộn thẻ được click vào chính giữa thanh trượt ngang
                centerActiveCategoryCard(this);
                
                // Gọi bộ lọc AJAX
                if (window.filterCategoryAjax) {
                    window.filterCategoryAjax(slug, href);
                }
            });
        });

        // Tự động cuộn thẻ danh mục đang active vào chính giữa khi nạp trang lần đầu
        const activeCard = document.querySelector('.category-card.active');
        if (activeCard) {
            setTimeout(() => {
                centerActiveCategoryCard(activeCard);
            }, 300);
        }

        // 7. Tự động cuộn xuống danh sách quán ăn khi người dùng lọc theo Danh mục trên Mobile
        @if(request()->has('cat'))
        setTimeout(() => {
            if (window.innerWidth <= 768) {
                const splitList = document.querySelector('.split-list');
                if (splitList) {
                    window.scrollTo({
                        top: splitList.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        }, 500);
        @endif

    });

    // 6. Hàm đồng bộ click card bên trái -> di chuyển camera map qua phải và mở popup marker tương ứng
    function focusOnEatery(lat, lng, slug, pName = null, pImg = null, pPrice = null, pStars = null, sellerName = null) {
        if (map && markers[slug]) {
            let targetLat = lat;
            if (window.innerWidth <= 768) {
                targetLat = lat + 0.0018;
            }

            if (pName && pImg) {
                const isOcop = (pName && (pName.includes('OCOP') || pName.includes('Đặc sản'))) || (sellerName && sellerName.includes('Chủ thể')) || (pStars && typeof pStars === 'string' && pStars.includes('sao'));
                const imgHtml = `<div style="position: relative; width: 100%; height: 155px; border-radius: 10px; overflow: hidden; border: 1px solid rgba(0,0,0,0.08);">
                    <img src="${pImg}" class="map-popup-img" style="width: 100%; height: 100%; object-fit: cover; object-position: center;" alt="${pName}">
                    ${isOcop ? '<span style="position: absolute; top: 8px; left: 8px; background: linear-gradient(135deg, #059669, #10b981); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 3px 9px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.25);">🌾 OCOP</span>' : ''}
                </div>`;

                const titleHtml = `<h4 class="map-popup-title" style="font-size: 0.95rem; font-weight: 800; color: #064e3b; margin: 6px 0 2px 0; line-height: 1.35;">${pName}</h4>`;
                const sellerHtml = sellerName ? `<div style="font-size: 0.72rem; font-weight: 700; color: #0284c7; margin-bottom: 4px;">🏛️ ${sellerName}</div>` : '';
                const starsHtml = pStars ? `<span class="ocop-star-tag" style="font-size: 0.7rem; padding: 2px 8px;">⭐ ${pStars}</span>` : '';
                const priceHtml = pPrice ? `<span style="font-size: 0.75rem; font-weight: 800; color: #059669;">${pPrice}</span>` : '';

                const isEduFocus = (window.STORYTELLING_SCHOOLS && window.STORYTELLING_SCHOOLS[slug]);
                const storyFocusBtn = isEduFocus 
                    ? `<button onclick="event.stopPropagation(); window.openSchoolStoryteller('${slug}', '/dia-diem/${slug}'); return false;" class="btn-secondary" style="padding: 4px 10px; font-size: 0.72rem; border-radius: 6px; font-family: var(--font-heading); background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.3); color: #4f46e5; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; transition: all 0.2s; font-weight: 700;">📖 Story</button>` 
                    : '';

                const actionBtnHtml = isOcop 
                    ? `<button onclick="event.stopPropagation(); window.openHomeOcopModalFromPopup('${slug}', '${pName.replace(/'/g, "\\'")}')" class="btn-primary" style="padding: 4px 10px; font-size: 0.72rem; border-radius: 6px; font-weight: 700; background: linear-gradient(135deg, #059669, #10b981); border: none; color: #fff; cursor: pointer;">Xem chi tiết</button>`
                    : `<a href="/dia-diem/${slug}" class="btn-primary" style="padding: 4px 10px; font-size: 0.72rem; border-radius: 6px; font-weight: 700;">Xem chi tiết</a>`;

                const customPopupHtml = `
                    <div class="map-popup-card">
                        ${imgHtml}
                        ${titleHtml}
                        ${sellerHtml}
                        <div class="map-popup-footer" style="margin-top: 4px; display: flex; align-items: center; justify-content: space-between;">
                            ${starsHtml || priceHtml}
                            <div style="display: flex; gap: 6px; align-items: center;">
                                ${storyFocusBtn}
                                ${actionBtnHtml}
                            </div>
                        </div>
                    </div>
                `;
                markers[slug].setPopupContent(customPopupHtml);
            }

            map.flyTo([targetLat, lng], 16, {
                animate: true,
                duration: 1.2
            });
            setTimeout(() => {
                markers[slug].openPopup();
            }, 1000);
            
            // Cuộn màn hình lên vị trí bản đồ trên mobile chuẩn xác ngay dưới thanh Header sticky (64px)
            if (window.innerWidth <= 992) {
                const mapContainer = document.querySelector('.split-map-container');
                if (mapContainer) {
                    window.scrollTo({
                        top: mapContainer.offsetTop - 64,
                        behavior: 'smooth'
                    });
                }
            }
        }
    }



    // 10. Logic lọc danh mục qua AJAX mượt mà (SPA style, không reload trang!)
    window.filterCategoryAjax = function(slug, href) {
        const eateriesContainer = document.getElementById('eateriesListContainer');
        const headerContainer = document.getElementById('listHeaderContainer');
        
        if (!eateriesContainer) return;
        
        // Thêm hiệu ứng mờ mượt khi tải
        eateriesContainer.style.opacity = '0.4';
        eateriesContainer.style.transition = 'opacity 0.2s ease';
        
        // Tạo URL request API
        const ajaxUrl = href + (href.includes('?') ? '&' : '?') + 'ajax=1';
        
        fetch(ajaxUrl)
            .then(res => res.json())
            .then(data => {
                // Cập nhật URL trình duyệt (không reload trang)
                history.pushState(null, '', href);
                
                // Vẽ lại markers trên bản đồ
                if (window.renderEateryMarkers) {
                    window.renderEateryMarkers(data.eateries);
                }
                
                const isOcopCategory = (slug === 'dong-anh-market');
                const isTraditionalMarket = (slug === 'traditional-market');
                const ocopProductsList = (data.ocopProducts && data.ocopProducts.length > 0) ? data.ocopProducts : [];

                // Cập nhật tiêu đề header của danh sách
                if (headerContainer) {
                    if (isOcopCategory) {
                        const countVal = ocopProductsList.length > 0 ? ocopProductsList.length : data.eateries.length;
                        const countText = ocopProductsList.length > 0 ? `${countVal} sản phẩm OCOP` : `${countVal} địa điểm`;
                        headerContainer.innerHTML = `
                            <div style="margin-bottom: 20px; border-bottom: 1.5px dashed rgba(212, 175, 55, 0.3); padding-bottom: 16px;">
                                <span class="heritage-badge" style="margin-bottom: 8px; font-size: 0.7rem; font-weight: 800; letter-spacing: 1.5px; border: 1px solid rgba(212, 175, 55, 0.4); background: rgba(212, 175, 55, 0.1); color: #ffb300; padding: 4px 10px; border-radius: 20px; display: inline-block;">🌾 NÔNG SẢN SỐ & ĐẶC SẢN OCOP</span>
                                <h2 style="font-size: 1.6rem; font-family: var(--font-heading); font-weight: 800; margin: 4px 0 6px 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                    <span style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Không Gian Sản Phẩm OCOP Đông Anh</span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal;">
                                        (${countText})
                                    </span>
                                </h2>
                                <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                                    Khám phá các sản phẩm OCOP đặc trưng, quà lưu niệm độc đáo, nông sản sạch mang đậm hồn quê Đông Anh.
                                </p>

                                <!-- Banner Trình Diễn Story Liên Hoàn Tất Cả Sản Phẩm OCOP -->
                                <div style="margin-top: 14px; background: linear-gradient(135deg, rgba(217, 119, 6, 0.15) 0%, rgba(5, 150, 105, 0.2) 100%); border: 1.5px solid rgba(251, 191, 36, 0.5); border-radius: 14px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; box-shadow: 0 8px 20px rgba(217, 119, 6, 0.15);">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 1.8rem;">🎬</span>
                                        <div>
                                            <h4 style="margin: 0; color: #d97706; font-size: 0.98rem; font-weight: 800; font-family: var(--font-heading);">HÀNH TRÌNH TỔNG THỂ DI SẢN OCOP ĐÔNG ANH</h4>
                                            <p style="margin: 2px 0 0 0; color: var(--text-muted); font-size: 0.8rem;">Xem trình diễn liên hoàn tất cả các vùng nguyên liệu & sản phẩm OCOP đạt sao trên bản đồ</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="window.openOcopFullHeritageStory()" style="background: linear-gradient(135deg, #d97706 0%, #059669 100%); border: none; color: #ffffff; padding: 9px 18px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4); transition: all 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                                        <span>🌾 Xem Story Tất Cả OCOP</span> ➔
                                    </button>
                                </div>
                            </div>
                        `;
                    } else if (isTraditionalMarket) {
                        headerContainer.innerHTML = `
                            <div class="traditional-market-hero-box">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <span class="market-badge-chip">
                                        <span>🏪</span> CHỢ SỐ
                                    </span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #0284c7; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #7dd3fc; box-shadow: 0 2px 8px rgba(14, 165, 233, 0.1);">
                                        📍 ${data.total || data.eateries.length} Chợ Quê & Trung Tâm Thương Mại
                                    </span>
                                </div>
                                
                                <h2 class="market-hero-title">
                                    Hệ Thống Chợ Số Đông Anh
                                </h2>
                                
                                <p style="font-size: 0.92rem; color: #334155; line-height: 1.65; margin: 0;">
                                    Khám phá nét đẹp văn hóa Chợ Quê Đông Anh kết hợp công nghệ Chuyển Đổi Số. Tra cứu sơ đồ gian hàng, bảng giá nông sản sạch, thanh toán quét mã VietQR không dùng tiền mặt và giao hàng tận nơi.
                                </p>

                                <div class="market-stats-pills">
                                    <div class="market-stat-pill">
                                        <span class="icon">✨</span> 100% Gian hàng chuẩn hóa
                                    </div>
                                    <div class="market-stat-pill">
                                        <span class="icon">📲</span> Thanh toán VietQR / Chuyển khoản
                                    </div>
                                    <div class="market-stat-pill">
                                        <span class="icon">🌱</span> Nông sản ATTP
                                    </div>
                                    <div class="market-stat-pill">
                                        <span class="icon">🗺️</span> Sơ đồ gian hàng 2D
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (slug === 'dong-anh-food-map') {
                        headerContainer.innerHTML = `
                            <div class="food-hero-box">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <span class="food-badge-chip">
                                        <span>🍜</span> ẨM THỰC ĐÔNG ANH
                                    </span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #ea580c; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #fdba74; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.1);">
                                        📍 ${data.total || data.eateries.length} Quán Ngon & Nhà Hàng Nổi Tiếng
                                    </span>
                                </div>
                                
                                <h2 class="food-hero-title">
                                    Bản Đồ Khám Phá Ẩm Thực Đông Anh
                                </h2>
                                
                                <p style="font-size: 0.92rem; color: #431407; line-height: 1.65; margin: 0;">
                                    Thưởng thức hương vị đậm đà đặc sản Đông Anh: Lẩu ếch măng cay, Quán nướng rặng tre, Bún chả làng quê... Đã được xác minh vệ sinh ATTP và đánh giá chất lượng thực tế.
                                </p>

                                <div class="food-stats-pills">
                                    <div class="food-stat-pill">
                                        <span class="icon">🔥</span> Quán ngon tuyển chọn
                                    </div>
                                    <div class="food-stat-pill">
                                        <span class="icon">⭐</span> Đánh giá thực tế
                                    </div>
                                    <div class="food-stat-pill">
                                        <span class="icon">🛡️</span> Chuẩn VSTP
                                    </div>
                                    <div class="food-stat-pill">
                                        <span class="icon">🛵</span> Đặt món & Chỉ đường
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (slug === 'stay-in-dong-anh') {
                        headerContainer.innerHTML = `
                            <div class="stay-hero-box">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <span class="stay-badge-chip"><span>🏨</span> STAY IN ĐÔNG ANH</span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #be185d; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #fbcfe8;">📍 ${data.total || data.eateries.length} Địa Điểm Lưu Trú & Khách Sạn</span>
                                </div>
                                <h2 class="stay-hero-title">Không Gian Lưu Trú & Nghỉ Dưỡng Đông Anh</h2>
                                <p style="font-size: 0.92rem; color: #831843; line-height: 1.65; margin: 0;">Trải nghiệm dịch vụ nghỉ dưỡng cao cấp, khách sạn đạt chuẩn, homestay ấm cúng ngợp tràn không gian xanh.</p>
                                <div class="stay-stats-pills">
                                    <div class="stay-stat-pill"><span class="icon">✨</span> Khách sạn & Homestay</div>
                                    <div class="stay-stat-pill"><span class="icon">⭐</span> Đạt chuẩn dịch vụ</div>
                                    <div class="stay-stat-pill"><span class="icon">🏊</span> Tiện ích hiện đại</div>
                                    <div class="stay-stat-pill"><span class="icon">🛎️</span> Đặt phòng nhanh</div>
                                </div>
                            </div>
                        `;
                    } else if (slug === 'wellness-care') {
                        headerContainer.innerHTML = `
                            <div class="wellness-hero-box">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <span class="wellness-badge-chip"><span>🩺</span> WELLNESS & CARE</span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #047857; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #a7f3d0;">📍 ${data.total || data.eateries.length} Cơ Sở Y Tế & Spa Chăm Sóc Sức Khỏe</span>
                                </div>
                                <h2 class="wellness-hero-title">Hệ Thống Y Tế & Chăm Sóc Sức Khỏe Đông Anh</h2>
                                <p style="font-size: 0.92rem; color: #064e3b; line-height: 1.65; margin: 0;">Tra cứu các bệnh viện uy tín, phòng khám đa khoa chất lượng cao, trung tâm spa & phục hồi sức khỏe được cấp phép.</p>
                                <div class="wellness-stats-pills">
                                    <div class="wellness-stat-pill"><span class="icon">🏥</span> Bệnh viện & Phòng khám</div>
                                    <div class="wellness-stat-pill"><span class="icon">🌿</span> Spa & Phục hồi sức khỏe</div>
                                    <div class="wellness-stat-pill"><span class="icon">👨‍⚕️</span> Bác sĩ chuyên khoa</div>
                                    <div class="wellness-stat-pill"><span class="icon">🚑</span> Hỗ trợ Y tế 24/7</div>
                                </div>
                            </div>
                        `;
                    } else if (slug === 'discover-dong-anh-community-culture-hub') {
                        headerContainer.innerHTML = `
                            <div class="culture-hero-box">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <span class="culture-badge-chip"><span>🏛️</span> COMMUNITY & CULTURE HUB</span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #b45309; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #fde047;">📍 ${data.total || data.eateries.length} Thiết Chế Văn Hóa - Thể Thao</span>
                                </div>
                                <h2 class="culture-hero-title">Trung Tâm Văn Hóa, Thể Thao & Sinh Hoạt Cộng Đồng</h2>
                                <p style="font-size: 0.92rem; color: #78350f; line-height: 1.65; margin: 0;">Không gian giao lưu văn hóa, nhà văn hóa huyện, sân vận động và các điểm sinh hoạt cộng đồng năng động.</p>
                                <div class="culture-stats-pills">
                                    <div class="culture-stat-pill"><span class="icon">🏛️</span> Nhà văn hóa & Sân vận động</div>
                                    <div class="culture-stat-pill"><span class="icon">🎨</span> Triển lãm & Sự kiện</div>
                                    <div class="culture-stat-pill"><span class="icon">⚽</span> Khu vui chơi thể thao</div>
                                    <div class="culture-stat-pill"><span class="icon">🤝</span> Kết nối cộng đồng</div>
                                </div>
                            </div>
                        `;
                    } else if (slug === 'smart-education-map') {
                        headerContainer.innerHTML = `
                            <div class="edu-hero-box">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <span class="edu-badge-chip"><span>🎓</span> SMART EDUCATION MAP</span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #4338ca; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #a5b4fc;">📍 ${data.total || data.eateries.length} Trường Học & Cơ Sở Giáo Dục</span>
                                </div>
                                <h2 class="edu-hero-title">Hệ Thống Mạng Lưới Giáo Dục & Trường Học Đông Anh</h2>
                                <p style="font-size: 0.92rem; color: #1e1b4b; line-height: 1.65; margin: 0;">Bản đồ thông minh tra cứu hệ thống các trường mầm non, tiểu học, THCS, THPT và trung tâm giáo dục chất lượng cao.</p>
                                <div class="edu-stats-pills">
                                    <div class="edu-stat-pill"><span class="icon">🏫</span> Trường đạt chuẩn Quốc gia</div>
                                    <div class="edu-stat-pill"><span class="icon">📚</span> Cơ sở vật chất hiện đại</div>
                                    <div class="edu-stat-pill"><span class="icon">👩‍🏫</span> Đội ngũ giáo viên giỏi</div>
                                    <div class="edu-stat-pill"><span class="icon">🗺️</span> Chỉ đường trường học</div>
                                </div>
                            </div>
                        `;
                    } else if (slug === 'co-so-kinh-doanh') {
                        headerContainer.innerHTML = `
                            <div class="business-hero-box">
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <span class="business-badge-chip">
                                        <span>🏪</span> CƠ SỞ KINH DOANH & DOANH NGHIỆP
                                    </span>
                                    <span id="resultsCountSpan" style="font-size: 0.85rem; font-weight: 700; color: #0284c7; background: #ffffff; padding: 5px 14px; border-radius: 20px; border: 1.5px solid #7dd3fc; box-shadow: 0 2px 8px rgba(14, 165, 233, 0.1);">
                                        📍 ${data.total || data.eateries.length} Hộ Kinh Doanh & Doanh Nghiệp Trên Địa Bàn
                                    </span>
                                </div>
                                
                                <h2 class="business-hero-title">
                                    Hệ Thống Cơ Sở Kinh Doanh & Doanh Nghiệp Đông Anh
                                </h2>
                                
                                <p style="font-size: 0.92rem; color: #334155; line-height: 1.65; margin: 0;">
                                    Tra cứu danh bạ Hộ kinh doanh cá thể, Cửa hàng dịch vụ, Siêu thị mini và Doanh nghiệp trên địa bàn xã Đông Anh. Kết nối giao thương số, công khai minh bạch mã số thuế và hỗ trợ chuyển đổi số bán lẻ toàn diện.
                                </p>

                                <div class="business-stats-pills">
                                    <div class="business-stat-pill">
                                        <span class="icon">🏢</span> 100% Xác thực MST & Ngành nghề
                                    </div>
                                    <div class="business-stat-pill">
                                        <span class="icon">📞</span> Hotline liên hệ trực tiếp
                                    </div>
                                    <div class="business-stat-pill">
                                        <span class="icon">💳</span> Thanh toán VietQR số
                                    </div>
                                    <div class="business-stat-pill">
                                        <span class="icon">🗺️</span> Bản đồ số & Chỉ đường
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        const activeCard = document.querySelector('.category-card.active');
                        let titleText = 'Địa điểm nổi bật <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; font-style: italic;">(Featured Places)</span>';
                        if (slug && activeCard) {
                            const titleEn = activeCard.querySelector('.cat-title-en') ? activeCard.querySelector('.cat-title-en').innerText : '';
                            const titleVi = activeCard.querySelector('.cat-title-vi') ? activeCard.querySelector('.cat-title-vi').innerText : '';
                            titleText = `${titleVi} <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; font-style: italic;">(${titleEn})</span>`;
                        }
                        
                        headerContainer.innerHTML = `
                            <h2 style="font-size: 1.25rem; margin: 6px 0 0 0; font-family: var(--font-heading); font-weight: 700; line-height: 1.4; color: var(--text-main);">
                                <span style="margin-right: 4px;">📍</span> 
                                ${titleText}
                                <span id="resultsCountSpan" style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-left: 6px; display: inline-block; white-space: nowrap;">
                                    (${data.eateries.length} địa điểm / places)
                                </span>
                            </h2>
                        `;
                    }
                }
                
                // Helper chọn hình ảnh thông minh theo ngành nghề cho JS
                function getSmartBusinessImgJS(name, desc) {
                    if (typeof window.getSmartBusinessImgJS === 'function') {
                        return window.getSmartBusinessImgJS(name, desc);
                    }
                    return 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80';
                }

                // Hàm sinh HTML danh sách Card
                window.buildEateryCardsHtml = function(eateriesList, catSlug) {
                    let cardsHtml = '';
                    const isTraditionalMarket = (catSlug === 'traditional-market');
                    const isOcopCategory = (catSlug === 'dong-anh-market');

                    eateriesList.forEach(eat => {
                        const categorySlug = eat.category ? (eat.category.slug || '') : (catSlug || '');
                        const isMarket = (categorySlug === 'traditional-market' || isTraditionalMarket);
                        const isOcopItem = (categorySlug === 'dong-anh-market' || isOcopCategory);

                        if (isOcopItem) {
                            let ocopCards = [];
                            if (eat.ocop_products && eat.ocop_products.length > 0) {
                                eat.ocop_products.forEach(p => {
                                    ocopCards.push({
                                        id: p.id,
                                        title: p.name,
                                        subtitle: 'Chủ thể sản xuất: ' + (p.seller_name || eat.name),
                                        desc: p.description || eat.description || '',
                                        image: p.image_path || eat.image_path || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80',
                                        stars: p.star_rating ? (p.star_rating.includes('sao') ? p.star_rating : p.star_rating + ' sao') : (eat.average_rating || '5.0'),
                                        price: p.price ? (isFinite(p.price) ? Number(p.price).toLocaleString('vi-VN') + 'đ' : p.price) : (eat.price_range || 'Liên hệ')
                                    });
                                });
                            } else {
                                const desc = eat.description || '';
                                const match = desc.match(/tên\s+sản\s+phẩm\s+OCOP:\s*([^;]+)/i);
                                if (match && match[1]) {
                                    const rawProducts = match[1].split(',').map(s => s.trim()).filter(Boolean);
                                    let cleanDesc = desc.replace(/tên\s+sản\s+phẩm\s+OCOP:\s*[^;]+;?\s*/i, '').replace(/^[^;]+;\s*địa chỉ[^;]+;\s*/i, '');
                                    if (!cleanDesc.trim()) cleanDesc = desc;

                                    rawProducts.forEach(pName => {
                                        ocopCards.push({
                                            title: pName,
                                            subtitle: 'Chủ thể sản xuất: ' + eat.name,
                                            desc: cleanDesc,
                                            image: eat.image_path || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80',
                                            stars: eat.average_rating || '5.0',
                                            price: eat.price_range || 'Liên hệ'
                                        });
                                    });
                                } else {
                                    const cleanName = eat.name.replace(/^(HKD|HTX|Hộ kinh doanh|Cơ sở|Công ty)\s+/i, '');
                                    ocopCards.push({
                                        title: 'Sản phẩm OCOP - ' + cleanName,
                                        subtitle: 'Chủ thể sản xuất: ' + eat.name,
                                        desc: desc,
                                        image: eat.image_path || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80',
                                        stars: eat.average_rating || '5.0',
                                        price: eat.price_range || 'Liên hệ'
                                    });
                                }
                            }

                            ocopCards.forEach(card => {
                                const communeName = eat.commune ? (eat.commune.name || eat.commune) : 'Đông Anh';
                                cardsHtml += `
                                    <div class="eatery-card glass-panel revealed hover-lift ocop-card-highlight" 
                                         data-slug="${eat.slug || ''}"
                                         data-name="${card.title}"
                                         data-address="${eat.address || ''}"
                                         data-desc="${card.desc}"
                                         data-commune="${communeName}"
                                         data-category="dong-anh-market"
                                         style="animation: fadeIn 0.4s ease forwards;"
                                         onclick="focusOnEatery(${eat.latitude || 21.1352}, ${eat.longitude || 105.8458}, '${eat.slug || ''}', '${card.title.replace(/'/g, "\\'")}', '${card.image}', '${card.price}', '${card.stars}', '${card.subtitle.replace(/'/g, "\\'")}')">
                                        <div class="eatery-img-wrapper hover-zoom-container">
                                            <img src="${card.image}" class="eatery-img hover-zoom-img" alt="${card.title}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80';">
                                        </div>
                                        <div class="eatery-info">
                                            <div style="margin-bottom: 4px;">
                                                <span class="ocop-title-badge">🌾 ĐẶC SẢN OCOP</span>
                                            </div>
                                            <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                                <h3 class="eatery-title ocop-product-title">${card.title}</h3>
                                                <div class="ocop-star-tag">
                                                    <span>⭐</span> ${card.stars}
                                                </div>
                                            </div>
                                            <div class="ocop-seller-badge">
                                                🏛️ ${card.subtitle}
                                            </div>
                                            ${card.desc && card.desc !== 'null' ? `<p class="eatery-desc">${card.desc}</p>` : ''}
                                            <div class="eatery-footer">
                                                <div class="eatery-meta-item">
                                                    <span>📍</span> ${communeName}
                                                </div>
                                                <div class="eatery-meta-item ocop-price-tag">
                                                    ${card.price}
                                                </div>
                                            </div>
                                            <a href="${card.id ? `/san-pham-ocop/${card.id}` : `/dia-diem/${eat.slug}`}" class="ocop-explore-btn" onclick="event.stopPropagation();">
                                                <span>🌾 Xem Chi Tiết Sản Phẩm OCOP</span> ➔
                                            </a>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            const isBusinessItem = (categorySlug === 'co-so-kinh-doanh' || catSlug === 'co-so-kinh-doanh');
                            let imgUrl = eat.image_path;
                            if (!imgUrl) {
                                if (isBusinessItem) {
                                    imgUrl = getSmartBusinessImgJS(eat.name, eat.description);
                                } else {
                                    imgUrl = 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80';
                                }
                            }

                            const ratingVal = eat.average_rating || (eat.rating ? parseFloat(eat.rating).toFixed(1) : '5.0');
                            const communeName = eat.commune ? (eat.commune.name || eat.commune) : '';
                            const categoryIcon = eat.category ? (eat.category.icon || '') : '';
                            const categoryName = eat.category ? (eat.category.name || '') : '';
                            
                            const isFoodItem = (categorySlug === 'dong-anh-food-map' || catSlug === 'dong-anh-food-map');
                            const isStayItem = (categorySlug === 'stay-in-dong-anh' || catSlug === 'stay-in-dong-anh');
                            const isWellnessItem = (categorySlug === 'wellness-care' || catSlug === 'wellness-care');
                            const isCultureItem = (categorySlug === 'discover-dong-anh-community-culture-hub' || catSlug === 'discover-dong-anh-community-culture-hub');
                            const isEduItem = (categorySlug === 'smart-education-map' || catSlug === 'smart-education-map');
                            const isCustomStyled = (isMarket || isFoodItem || isStayItem || isWellnessItem || isCultureItem || isEduItem || isBusinessItem);

                            // Metadata xử lý cho Doanh nghiệp / Hộ kinh doanh
                            let storyData = eat.storytelling_data;
                            if (typeof storyData === 'string') {
                                try { storyData = JSON.parse(storyData); } catch(e) { storyData = {}; }
                            }
                            storyData = storyData || {};
                            const taxCode = storyData.tax_code || '';
                            const isEnterprise = (eat.name || '').toUpperCase().includes('CÔNG TY') || (storyData.business_type === 'Doanh nghiệp');

                            cardsHtml += `
                                <div class="eatery-card glass-panel revealed hover-lift ${isMarket ? 'market-card-highlight' : ''} ${isFoodItem ? 'food-card-highlight' : ''} ${isStayItem ? 'stay-card-highlight' : ''} ${isWellnessItem ? 'wellness-card-highlight' : ''} ${isCultureItem ? 'culture-card-highlight' : ''} ${isEduItem ? 'edu-card-highlight' : ''} ${isBusinessItem ? 'business-card-highlight' : ''}" 
                                     data-slug="${eat.slug}"
                                     data-name="${eat.name}"
                                     data-address="${eat.address}"
                                     data-desc="${eat.description && eat.description !== 'null' ? eat.description : ''}"
                                     data-commune="${communeName}"
                                     data-category="${categorySlug}"
                                     style="animation: fadeIn 0.4s ease forwards;"
                                     onclick="focusOnEatery(${eat.latitude}, ${eat.longitude}, '${eat.slug}')">
                                    <div class="eatery-img-wrapper hover-zoom-container">
                                        <img src="${imgUrl}" class="eatery-img hover-zoom-img" alt="${eat.name}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80';">
                                        ${!isCustomStyled ? `
                                            <div style="position: absolute; top: 8px; left: 8px; max-width: calc(100% - 16px); display: flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 700; color: #ffffff; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); padding: 4px 8px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);">
                                                <span>${categoryIcon}</span>
                                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${categoryName}</span>
                                            </div>
                                        ` : ''}
                                    </div>
                                    <div class="eatery-info">
                                        ${isMarket ? `
                                            <div style="margin-bottom: 4px;">
                                                <span class="market-title-badge">🏪 CHỢ SỐ</span>
                                            </div>
                                        ` : isFoodItem ? `
                                            <div style="margin-bottom: 4px;">
                                                <span class="food-title-badge">🍜 QUÁN NGON NỔI BẬT</span>
                                            </div>
                                        ` : isStayItem ? `
                                            <div style="margin-bottom: 4px;">
                                                <span class="stay-title-badge">🏨 LƯU TRÚ DỊCH VỤ</span>
                                            </div>
                                        ` : isWellnessItem ? `
                                            <div style="margin-bottom: 4px;">
                                                <span class="wellness-title-badge">🩺 CHĂM SÓC SỨC KHỎE</span>
                                            </div>
                                        ` : isCultureItem ? `
                                            <div style="margin-bottom: 4px;">
                                                <span class="culture-title-badge">🏛️ THIẾT CHẾ VĂN HÓA</span>
                                            </div>
                                        ` : isEduItem ? `
                                            <div style="margin-bottom: 4px;">
                                                <span class="edu-title-badge">🎓 CƠ SỞ GIÁO DỤC</span>
                                            </div>
                                        ` : isBusinessItem ? `
                                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; margin-bottom: 4px;">
                                                <span class="business-title-badge">
                                                    ${isEnterprise ? '🏢 DOANH NGHIỆP' : '🏪 HỘ KINH DOANH'}
                                                </span>
                                                ${taxCode ? `<span class="business-mst-tag">🆔 MST: ${taxCode}</span>` : ''}
                                            </div>
                                        ` : ''}
                                        <div class="eatery-header" ${isCustomStyled ? 'style="align-items: center; margin-bottom: 6px;"' : ''}>
                                            <h3 class="eatery-title" ${isBusinessItem ? 'style="color: #0f172a; font-size: 1.18rem; font-weight: 900; letter-spacing: -0.2px;"' : ''}>${eat.name}</h3>
                                            <div class="rating-stars" ${isFoodItem ? 'style="color: #f59e0b; font-weight: 800;"' : isStayItem ? 'style="color: #db2777; font-weight: 800;"' : isWellnessItem ? 'style="color: #059669; font-weight: 800;"' : isCultureItem ? 'style="color: #d97706; font-weight: 800;"' : isEduItem ? 'style="color: #4f46e5; font-weight: 800;"' : isBusinessItem ? 'style="color: #0284c7; font-weight: 800;"' : ''}>
                                                <span>⭐</span> ${ratingVal}
                                            </div>
                                        </div>
                                        ${eat.description && eat.description !== 'null' ? `<p class="eatery-desc">${eat.description}</p>` : ''}
                                        <div class="eatery-footer">
                                            <div class="eatery-meta-item">
                                                <span>📍</span> ${communeName}
                                            </div>
                                            ${isBusinessItem && eat.phone ? `
                                                <a href="tel:${eat.phone}" class="business-phone-tag" onclick="event.stopPropagation();">
                                                    <span>📞</span> ${eat.phone}
                                                </a>
                                            ` : !['smart-education-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub'].includes(categorySlug) ? `
                                                <div class="eatery-meta-item" style="${isFoodItem ? 'color: #ea580c; font-weight: 700;' : 'color: var(--primary); font-weight: 600;'}">
                                                    ${eat.price_range}
                                                </div>
                                            ` : ''}
                                        </div>
                                        ${isMarket ? `
                                            <a href="/dia-diem/${eat.slug}" class="market-explore-btn" onclick="event.stopPropagation();">
                                                <span>🛒 Xem Gian Hàng Số & Sơ Đồ Chợ</span> ➔
                                            </a>
                                        ` : isFoodItem ? `
                                            <a href="/dia-diem/${eat.slug}" class="food-explore-btn" onclick="event.stopPropagation();">
                                                <span>🍽️ Xem Thực Đơn & Chỉ Đường</span> ➔
                                            </a>
                                        ` : isStayItem ? `
                                            <a href="/dia-diem/${eat.slug}" class="stay-explore-btn" onclick="event.stopPropagation();">
                                                <span>🏨 Xem Chi Tiết & Đặt Phòng</span> ➔
                                            </a>
                                        ` : isWellnessItem ? `
                                            <a href="/dia-diem/${eat.slug}" class="wellness-explore-btn" onclick="event.stopPropagation();">
                                                <span>🩺 Xem Dịch Vụ & Đặt Lịch</span> ➔
                                            </a>
                                        ` : isCultureItem ? `
                                            <a href="/dia-diem/${eat.slug}" class="culture-explore-btn" onclick="event.stopPropagation();">
                                                <span>🏛️ Khám Phá Hoạt Động & Sự Kiện</span> ➔
                                            </a>
                                        ` : isEduItem ? `
                                            <div class="edu-card-actions">
                                                <button type="button" class="edu-story-btn" onclick="event.stopPropagation(); window.openSchoolStoryteller('${eat.slug}', '/dia-diem/${eat.slug}');">
                                                    <span>📖 Xem Story</span>
                                                </button>
                                                <a href="/dia-diem/${eat.slug}" class="edu-explore-btn" onclick="event.stopPropagation();">
                                                    <span>🎓 Tra Cứu Thông Tin Trường</span> ➔
                                                </a>
                                            </div>
                                        ` : isBusinessItem ? `
                                            <a href="/dia-diem/${eat.slug}" class="business-explore-btn" onclick="event.stopPropagation();">
                                                <span>🏪 Xem Chi Tiết Cơ Sở & Dịch Vụ</span> ➔
                                            </a>
                                        ` : `
                                            <a href="/dia-diem/${eat.slug}" class="btn-primary" onclick="event.stopPropagation();" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; text-decoration: none; margin-top: 10px;">
                                                <span>Xem Chi Tiết</span> ➔
                                            </a>
                                        `}
                                    </div>
                                </div>
                            `;
                        }
                    });
                    return cardsHtml;
                };

                // Re-render danh sách khi lọc qua AJAX
                if (isOcopCategory && ocopProductsList.length > 0) {
                    let cardsHtml = '';
                    ocopProductsList.forEach(p => {
                        const eat = p.eatery || {};
                        const pName = p.name || 'Sản phẩm OCOP';
                        const sellerName = p.seller_name || eat.name || 'Đông Anh';
                        const imgUrl = p.image_path ? p.image_path : (eat.image_path ? eat.image_path : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80');
                        const stars = p.star_rating ? (p.star_rating.includes('sao') ? p.star_rating : p.star_rating + ' sao') : 'Đặc sản OCOP';
                        const formattedPrice = p.price ? (isFinite(p.price) ? Number(p.price).toLocaleString('vi-VN') + 'đ' : p.price) : (eat.price_range || 'Liên hệ');
                        const communeName = eat.commune ? (eat.commune.name || eat.commune) : 'Đông Anh';
                        const slug = eat.slug || '';
                        const lat = eat.latitude || 21.1352;
                        const lng = eat.longitude || 105.8458;

                        cardsHtml += `
                            <div class="eatery-card glass-panel revealed hover-lift ocop-card-highlight" 
                                 data-slug="${slug}"
                                 data-name="${pName}"
                                 data-address="${eat.address || ''}"
                                 data-desc="${p.description || eat.description || ''}"
                                 data-commune="${communeName}"
                                 data-category="dong-anh-market"
                                 style="animation: fadeIn 0.4s ease forwards;"
                                 onclick="focusOnEatery(${lat}, ${lng}, '${slug}', '${pName.replace(/'/g, "\\'")}', '${imgUrl}', '${formattedPrice}', '${stars}', '${sellerName.replace(/'/g, "\\'")}')">
                                <div class="eatery-img-wrapper hover-zoom-container">
                                    <img src="${imgUrl}" class="eatery-img hover-zoom-img" alt="${pName}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80';">
                                </div>
                                <div class="eatery-info">
                                    <div style="margin-bottom: 4px;">
                                        <span class="ocop-title-badge">🌾 ĐẶC SẢN OCOP</span>
                                    </div>
                                    <div class="eatery-header" style="align-items: center; margin-bottom: 6px;">
                                        <h3 class="eatery-title ocop-product-title">${pName}</h3>
                                        <div class="ocop-star-tag">
                                            <span>⭐</span> ${stars}
                                        </div>
                                    </div>
                                    <div class="ocop-seller-badge">
                                        🏛️ Chủ thể sản xuất: ${sellerName}
                                    </div>
                                    ${p.description || eat.description ? `<p class="eatery-desc">${p.description || eat.description}</p>` : ''}
                                    <div class="eatery-footer">
                                        <div class="eatery-meta-item">
                                            <span>📍</span> ${communeName}
                                        </div>
                                        <div class="eatery-meta-item ocop-price-tag">
                                            ${formattedPrice}
                                        </div>
                                    </div>
                                    <a href="/san-pham-ocop/${p.id}" class="ocop-explore-btn" onclick="event.stopPropagation();">
                                        <span>🌾 Xem Chi Tiết Sản Phẩm OCOP</span> ➔
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    eateriesContainer.innerHTML = cardsHtml;
                } else if (data.eateries.length > 0) {
                    eateriesContainer.innerHTML = window.buildEateryCardsHtml(data.eateries, slug);
                } else {
                    eateriesContainer.innerHTML = `
                        <div class="glass-panel" style="padding: 40px; text-align: center; color: var(--text-muted); width: 100%;">
                            <p style="font-size: 1.2rem; margin-bottom: 8px; color: var(--text-main);">😔 Không tìm thấy địa điểm nào phù hợp</p>
                            <p style="font-size: 0.9rem;">Hãy thử lọc danh mục khác hoặc xóa bộ lọc để khám phá lại toàn bộ Đông Anh!</p>
                            <a href="/" class="btn-primary" style="margin-top: 16px; padding: 8px 16px; text-decoration: none; display: inline-block;">Xem tất cả</a>
                        </div>
                    `;
                    const viewAllBtn = eateriesContainer.querySelector('a');
                    if (viewAllBtn) {
                        viewAllBtn.addEventListener('click', function(evt) {
                            evt.preventDefault();
                            const allCatCard = document.querySelector('.category-card[href="/"]');
                            if (allCatCard) allCatCard.click();
                        });
                    }
                }

                // Cập nhật lại Infinite Scroll State cho danh mục mới
                if (window.infiniteScrollState) {
                    window.infiniteScrollState.currentPage = data.page || 1;
                    window.infiniteScrollState.currentCat = slug || '';
                    window.infiniteScrollState.totalCount = data.total || data.eateries.length;
                    window.infiniteScrollState.loadedCount = data.eateries.length;
                    window.infiniteScrollState.hasMore = data.has_more !== undefined ? data.has_more : false;
                    window.infiniteScrollState.isLoading = false;

                    const allLoadedInd = document.getElementById('allLoadedIndicator');
                    if (allLoadedInd) {
                        allLoadedInd.style.display = !window.infiniteScrollState.hasMore ? 'block' : 'none';
                        const totalLoadedSpan = document.getElementById('totalLoadedSpan');
                        if (totalLoadedSpan) totalLoadedSpan.innerText = window.infiniteScrollState.totalCount;
                    }
                }
                
                // Mở lại độ mờ
                eateriesContainer.style.opacity = '1';
                
                // Tự động cuộn xuống danh sách quán ăn trên di động
                if (window.innerWidth <= 768) {
                    const splitList = document.querySelector('.split-list');
                    if (splitList) {
                        window.scrollTo({
                            top: splitList.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                }
            })
            .catch(err => {
                console.error("AJAX loading error:", err);
                eateriesContainer.style.opacity = '1';
            });
    };

    // ==========================================================================
    // INFINITE SCROLL ENGINE (TỰ ĐỘNG NẠP TIẾP ĐỊA ĐIỂM KHI CUỘN CHUỘT)
    // ==========================================================================
    window.infiniteScrollState = {
        currentPage: {{ $page ?? 1 }},
        perPage: {{ $perPage ?? 24 }},
        totalCount: {{ $totalCount ?? count($eateries) }},
        hasMore: {{ (isset($totalCount) && count($eateries) < $totalCount) ? 'true' : 'false' }},
        isLoading: false,
        currentCat: '{{ $selectedCatSlug ?? "" }}',
        currentCom: '{{ $selectedComSlug ?? "" }}',
        loadedCount: {{ count($eateries) }}
    };

    window.loadNextInfinitePage = function() {
        if (window.infiniteScrollState.isLoading || !window.infiniteScrollState.hasMore) return;

        window.infiniteScrollState.isLoading = true;
        const loader = document.getElementById('infiniteScrollLoader');
        const allLoadedInd = document.getElementById('allLoadedIndicator');
        if (loader) loader.style.display = 'block';

        const nextPage = window.infiniteScrollState.currentPage + 1;
        let url = `/?ajax=1&page=${nextPage}`;
        if (window.infiniteScrollState.currentCat) url += `&cat=${encodeURIComponent(window.infiniteScrollState.currentCat)}`;
        if (window.infiniteScrollState.currentCom) url += `&com=${encodeURIComponent(window.infiniteScrollState.currentCom)}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                const eateriesContainer = document.getElementById('eateriesListContainer');
                if (data.eateries && data.eateries.length > 0 && eateriesContainer) {
                    const newCardsHtml = window.buildEateryCardsHtml(data.eateries, window.infiniteScrollState.currentCat);
                    const tempWrapper = document.createElement('div');
                    tempWrapper.innerHTML = newCardsHtml;
                    while (tempWrapper.firstChild) {
                        eateriesContainer.appendChild(tempWrapper.firstChild);
                    }

                    // Thêm markers mới vào bản đồ Leaflet
                    if (window.appendEateryMarkers) {
                        window.appendEateryMarkers(data.eateries);
                    }

                    window.infiniteScrollState.currentPage = nextPage;
                    window.infiniteScrollState.loadedCount += data.eateries.length;
                    window.infiniteScrollState.hasMore = data.has_more;
                    window.infiniteScrollState.totalCount = data.total;

                    // Cập nhật số lượng đếm trên header
                    const countSpan = document.getElementById('resultsCountSpan');
                    if (countSpan) {
                        countSpan.innerText = `(${window.infiniteScrollState.loadedCount} / ${window.infiniteScrollState.totalCount} địa điểm / places)`;
                    }
                } else {
                    window.infiniteScrollState.hasMore = false;
                }

                if (loader) loader.style.display = 'none';
                if (!window.infiniteScrollState.hasMore && allLoadedInd) {
                    const totalLoadedSpan = document.getElementById('totalLoadedSpan');
                    if (totalLoadedSpan) totalLoadedSpan.innerText = window.infiniteScrollState.totalCount;
                    allLoadedInd.style.display = 'block';
                }
                window.infiniteScrollState.isLoading = false;
            })
            .catch(err => {
                console.error("Infinite scroll error:", err);
                if (loader) loader.style.display = 'none';
                window.infiniteScrollState.isLoading = false;
            });
    };

    // Kích hoạt Infinite Scroll đa nền tảng (Desktop .split-list và Mobile window)
    function initInfiniteScroll() {
        const sentinel = document.getElementById('infiniteScrollSentinel');
        const splitList = document.querySelector('.split-list');

        if (sentinel && 'IntersectionObserver' in window) {
            // 1. Observer cho vùng cuộn riêng .split-list (Desktop)
            if (splitList) {
                const listObserver = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        window.loadNextInfinitePage();
                    }
                }, { root: splitList, rootMargin: '400px' });
                listObserver.observe(sentinel);
            }

            // 2. Observer cho viewport toàn trang (Mobile)
            const winObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    window.loadNextInfinitePage();
                }
            }, { rootMargin: '400px' });
            winObserver.observe(sentinel);
        }

        // 3. Scroll listener trực tiếp trên .split-list (Desktop)
        if (splitList) {
            splitList.addEventListener('scroll', function() {
                if (window.infiniteScrollState.isLoading || !window.infiniteScrollState.hasMore) return;
                if (splitList.scrollTop + splitList.clientHeight >= splitList.scrollHeight - 450) {
                    window.loadNextInfinitePage();
                }
            }, { passive: true });
        }

        // 4. Scroll listener trực tiếp trên window (Mobile)
        window.addEventListener('scroll', function() {
            if (window.infiniteScrollState.isLoading || !window.infiniteScrollState.hasMore) return;
            const scrollY = window.scrollY || window.pageYOffset;
            const docHeight = document.documentElement.scrollHeight;
            const winHeight = window.innerHeight;
            if (scrollY + winHeight >= docHeight - 450) {
                window.loadNextInfinitePage();
            }
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initInfiniteScroll);
    } else {
        initInfiniteScroll();
    }

    // ==========================================================================
    // DYNAMIC TIKTOK REELS-STYLE PLAYER FOR TÓP TÓP FOOD TOUR
    // ==========================================================================
    let reelsList = [];
    // 7. Immersive TikTok Reels Video Player
    let currentLikeCount = 3800;
    let isLiked = false;
    let currentEateryReels = [];
    let currentReelIndex = 0;
    let currentEateryName = '';
    let currentSpecialtyName = '';

    // Helper functions to extract video IDs
    function getTikTokVideoId(url) {
        // Match any sequence of 15 to 22 digits which represents a TikTok video ID
        const matches = url.match(/\d{15,22}/);
        if (matches && matches[0]) return matches[0];
        return null;
    }

    function getYouTubeShortsId(url) {
        const regExp = /^.*(shorts\/|youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        return (match && match[1] && match[2] && match[2].length === 11) ? match[2] : (match && match[1] && match[1].length === 11 ? match[1] : null);
    }

    window.openReelsModal = function(eaterySlug, eateryName, specialtyName, imagePath) {
        currentEateryName = eateryName;
        currentSpecialtyName = specialtyName;
        
        // Find eatery and its approved videos
        const eat = eateries.find(e => e.slug === eaterySlug);
        let approvedVideos = [];
        if (eat && eat.review_videos) {
            approvedVideos = eat.review_videos;
        }
        
        if (approvedVideos.length === 0) {
            // Fallback mock video if no specific video is linked
            currentEateryReels = [{
                video_url: 'https://assets.mixkit.co/videos/preview/mixkit-chef-preparing-a-fresh-vegetable-salad-32860-large.mp4',
                video_type: 'local'
            }];
        } else {
            currentEateryReels = approvedVideos;
        }
        
        currentReelIndex = 0;
        document.getElementById('reelsModal').style.display = 'flex';
        
        playReelAtIndex(currentReelIndex);
    };

    function playReelAtIndex(index) {
        if (index < 0 || index >= currentEateryReels.length) return;
        
        const reel = currentEateryReels[index];
        document.getElementById('reelsEateryName').innerText = currentEateryName;
        
        // Dynamic description including video count
        const videoIndicator = currentEateryReels.length > 1 ? `[Video ${index + 1}/${currentEateryReels.length}] ` : '';
        document.getElementById('reelsVideoDesc').innerText = `${videoIndicator}Khám phá món ngon tại "${currentEateryName}". Đặc sản "${currentSpecialtyName}" đang làm nức lòng thực khách gần xa bởi hương vị đậm chất truyền thống Đông Anh!`;
        document.querySelector('.reels-signature-tag').innerText = `🌟 Món đặc trưng: ${currentSpecialtyName}`;
        
        // Update inner navigation arrows visibility (Universal & Mobile helper)
        const mPrevBtn = document.getElementById('modalPrevReelBtn');
        const mNextBtn = document.getElementById('modalNextReelBtn');
        if (mPrevBtn && mNextBtn) {
            mPrevBtn.style.display = index > 0 ? 'flex' : 'none';
            mNextBtn.style.display = index < currentEateryReels.length - 1 ? 'flex' : 'none';
        }

        // Update Instagram-style top progress bars
        const progressContainer = document.getElementById('reelsProgressBars');
        if (progressContainer) {
            progressContainer.innerHTML = '';
            if (currentEateryReels.length > 1) {
                for (let i = 0; i < currentEateryReels.length; i++) {
                    const bar = document.createElement('div');
                    bar.style.flex = '1';
                    bar.style.height = '3px';
                    bar.style.borderRadius = '2px';
                    bar.style.background = i === index ? '#ffffff' : 'rgba(255,255,255,0.35)';
                    bar.style.transition = 'background 0.3s ease';
                    progressContainer.appendChild(bar);
                }
            }
        }
        
        const wrapper = document.getElementById('reelPlayerWrapper');
        wrapper.innerHTML = ''; // Clear previous player
        
        let videoUrl = reel.video_url;
        let videoType = reel.video_type;

        // Dynamically hide/show the right side action panel for YouTube videos
        const sideActions = document.querySelector('.reels-side-actions');
        const tapOverlay = document.getElementById('reelTapOverlay');
        const isIframe = videoType === 'youtube_shorts' || videoType === 'tiktok' || videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be');
        
        if (sideActions) {
            if (isIframe) {
                sideActions.style.display = 'none';
            } else {
                sideActions.style.display = 'flex';
            }
        }

        // Allow touch, click, zoom gestures to go directly to YouTube/TikTok player by setting pointer-events: none
        if (tapOverlay) {
            if (isIframe) {
                tapOverlay.style.pointerEvents = 'none';
            } else {
                tapOverlay.style.pointerEvents = 'auto';
            }
        }

        if (videoType === 'tiktok') {
            const videoId = getTikTokVideoId(videoUrl);
            if (videoId) {
                wrapper.innerHTML = `<iframe src="https://www.tiktok.com/embed/v2/${videoId}" style="width: 100%; height: 100%; border: none; background: #000; pointer-events: auto;" allowfullscreen allow="autoplay; encrypted-media;"></iframe>`;
            } else {
                wrapper.innerHTML = `<iframe src="${videoUrl}" style="width: 100%; height: 100%; border: none; background: #000; pointer-events: auto;" allowfullscreen allow="autoplay;"></iframe>`;
            }
        } else if (videoType === 'youtube_shorts' || videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
            const shortsId = getYouTubeShortsId(videoUrl);
            if (shortsId) {
                wrapper.innerHTML = `<iframe src="https://www.youtube.com/embed/${shortsId}?autoplay=1" style="width: 100%; height: 100%; border: none; background: #000; pointer-events: auto;" allowfullscreen allow="autoplay; encrypted-media;"></iframe>`;
            } else {
                wrapper.innerHTML = `<iframe src="${videoUrl}" style="width: 100%; height: 100%; border: none; background: #000; pointer-events: auto;" allowfullscreen allow="autoplay;"></iframe>`;
            }
        } else {
            // Local direct mp4 storage file
            wrapper.innerHTML = `<video src="${videoUrl}" autoplay loop muted playsinline controls style="width: 100%; height: 100%; object-fit: cover; background: #000; pointer-events: auto;"></video>`;
        }
    }

    window.searchPrevReel = function() {
        if (currentReelIndex > 0) {
            currentReelIndex--;
            playReelAtIndex(currentReelIndex);
        }
    };

    window.searchNextReel = function() {
        if (currentReelIndex < currentEateryReels.length - 1) {
            currentReelIndex++;
            playReelAtIndex(currentReelIndex);
        }
    };

    window.closeReelsModal = function() {
        document.getElementById('reelPlayerWrapper').innerHTML = ''; // Clear player
        document.getElementById('reelsModal').style.display = 'none';
    };

    window.toggleReelsLike = function() {
        const likeBtn = document.getElementById('reelsLikeBtn');
        const countSpan = document.getElementById('reelsLikeCount');
        
        if (isLiked) {
            isLiked = false;
            currentLikeCount--;
            likeBtn.querySelector('span').style.color = '#fff';
        } else {
            isLiked = true;
            currentLikeCount++;
            likeBtn.querySelector('span').style.color = '#ff3366';
            
            // Tim bay từ tâm
            spawnSingleHeart(window.innerWidth / 2, window.innerHeight / 2);
        }
        countSpan.innerText = (currentLikeCount / 1000).toFixed(1) + 'K';
    };

    window.triggerDoubleTapHeart = function(event) {
        spawnSingleHeart(event.clientX, event.clientY);
        if (!isLiked) {
            toggleReelsLike();
        }
    };

    function spawnSingleHeart(x, y) {
        const container = document.getElementById('reelsModal');
        const heart = document.createElement('div');
        heart.className = 'floating-heart';
        heart.innerHTML = '❤️';
        heart.style.left = x + 'px';
        heart.style.top = y + 'px';
        
        const dx = (Math.random() * 120 - 60) + 'px';
        const rot = (Math.random() * 70 - 35) + 'deg';
        heart.style.setProperty('--dx', dx);
        heart.style.setProperty('--rot', rot);
        
        container.appendChild(heart);
        setTimeout(() => heart.remove(), 1000);
    }

    // ----------------------------------------------------
    // OCOP Product Modal Logic (Home Page & Maps)
    // ----------------------------------------------------
    @php
        $allOcopProductsData = [];
        try {
            $allOcopList = \App\Models\OcopProduct::whereNotNull('star_rating')
                ->where('star_rating', '!=', '')
                ->whereHas('eatery.category', function($q) {
                    $q->where('slug', 'dong-anh-market');
                })
                ->with('eatery.commune')
                ->get();
            foreach ($allOcopList as $p) {
                $eat = $p->eatery;
                $allOcopProductsData[] = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'star_rating' => $p->star_rating ? (str_contains($p->star_rating, 'sao') ? $p->star_rating : $p->star_rating . ' sao') : '3 sao',
                    'price' => $p->price ? (is_numeric($p->price) ? number_format($p->price, 0, ',', '.') . 'đ' : $p->price) : ($eat ? $eat->price_range : 'Liên hệ'),
                    'image' => $p->image_path ?: ($eat && $eat->image_path ? $eat->image_path : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80'),
                    'seller_name' => $p->seller_name ?: ($eat ? $eat->name : 'Cơ sở sản xuất Đông Anh'),
                    'address' => $eat ? ($eat->address ?: ($eat->commune ? $eat->commune->name . ', Đông Anh' : 'Đông Anh, Hà Nội')) : 'Đông Anh, Hà Nội',
                    'phone' => $p->phone ?: ($eat ? $eat->phone : ''),
                    'description' => $p->description ?: ($eat ? $eat->description : 'Chưa có bài viết mô tả chi tiết sản phẩm.'),
                    'story' => $p->story,
                    'artisans' => $p->artisans,
                    'ingredients' => $p->ingredients,
                    'timeline' => $p->timeline,
                    'fun_fact' => $p->fun_fact,
                    'slug' => $eat ? $eat->slug : '',
                    'lat' => $p->latitude ? (float)$p->latitude : ($eat && $eat->latitude ? (float)$eat->latitude : null),
                    'lng' => $p->longitude ? (float)$p->longitude : ($eat && $eat->longitude ? (float)$eat->longitude : null),
                ];
            }
        } catch(\Exception $e) {}
    @endphp

    window.OCOP_PRODUCTS = {!! json_encode($allOcopProductsData) !!};

    window.openHomeOcopModalFromPopup = function(slug, pName) {
        let found = null;
        if (window.OCOP_PRODUCTS && window.OCOP_PRODUCTS.length > 0) {
            found = window.OCOP_PRODUCTS.find(p => (pName && p.name && p.name.toLowerCase().trim() === pName.toLowerCase().trim()) || (p.slug && p.slug === slug));
            if (!found) {
                found = window.OCOP_PRODUCTS.find(p => pName && p.name && p.name.toLowerCase().includes(pName.toLowerCase()));
            }
        }
        if (!found) {
            found = {
                name: pName || 'Sản phẩm OCOP',
                seller_name: 'Hộ kinh doanh Đông Anh',
                image: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80',
                star_rating: '3 sao',
                price: 'Theo yêu cầu',
                description: 'Thông tin chi tiết về sản phẩm OCOP Đông Anh.',
                slug: slug
            };
        }
        openHomeOcopModal(found);
    };

    window.openHomeOcopModal = function(product) {
        document.getElementById('hpmName').textContent = product.name || 'Sản phẩm OCOP';
        document.getElementById('hpmImg').src = product.image || 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80';
        document.getElementById('hpmStars').textContent = product.star_rating ? ('⭐ ' + product.star_rating) : '🏪 Cơ sở kinh doanh';
        document.getElementById('hpmPrice').textContent = product.price || 'Liên hệ';
        document.getElementById('hpmSeller').textContent = product.seller_name || 'Cơ sở sản xuất Đông Anh';
        document.getElementById('hpmAddress').textContent = '📍 ' + (product.address || 'Đông Anh, Hà Nội');
        
        // Call phone button
        const callBtn = document.getElementById('hpmCallBtn');
        if (product.phone) {
            callBtn.href = 'tel:' + product.phone;
            callBtn.innerHTML = '📞 Gọi hotline: ' + product.phone;
            callBtn.style.display = 'inline-flex';
        } else {
            callBtn.style.display = 'none';
        }

        // Eatery page link
        const eateryBtn = document.getElementById('hpmEateryLink');
        if (product.slug) {
            eateryBtn.href = '/dia-diem/' + product.slug;
            eateryBtn.style.display = 'inline-flex';
        } else {
            eateryBtn.style.display = 'none';
        }

        // Description & Story
        let fullDesc = product.story || product.description || 'Chưa cập nhật mô tả chi tiết.';
        document.getElementById('hpmDesc').innerText = fullDesc;

        // Ingredients & Secret Section
        const ingSection = document.getElementById('hpmIngSection');
        const ingGrid = document.getElementById('hpmIngGrid');
        ingGrid.innerHTML = '';

        let ingredientsArray = [];
        if (Array.isArray(product.ingredients)) {
            ingredientsArray = product.ingredients;
        } else if (typeof product.ingredients === 'string') {
            try { ingredientsArray = JSON.parse(product.ingredients) || []; } catch(e) {}
        }

        if (ingredientsArray && ingredientsArray.length > 0) {
            ingredientsArray.forEach(ing => {
                const div = document.createElement('div');
                div.style.cssText = 'padding: 10px 14px; background: #fefce8; border: 1px solid #fef08a; color: #854d0e; display: flex; align-items: center; gap: 8px; border-radius: 8px; font-size: 0.88rem; font-weight: 600;';
                div.innerHTML = `<span>✨</span><span>${ing}</span>`;
                ingGrid.appendChild(div);
            });
            ingSection.style.display = 'block';
        } else {
            ingSection.style.display = 'none';
        }

        // Show modal
        const modal = document.getElementById('homeOcopProductModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.children[0].style.transform = 'scale(1)';
        }, 10);
        document.body.style.overflow = 'hidden';
    };

    window.closeHomeOcopModal = function() {
        const modal = document.getElementById('homeOcopProductModal');
        modal.style.opacity = '0';
        modal.children[0].style.transform = 'scale(0.92)';
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    };

    // 🚀 Performance Optimizer: Pause background 3D animations when scrolled out of viewport
    document.addEventListener('DOMContentLoaded', () => {
        const heroBanner = document.querySelector('.custom-hero-banner');
        if (heroBanner && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        heroBanner.classList.remove('hero-paused');
                    } else {
                        heroBanner.classList.add('hero-paused');
                    }
                });
            }, { threshold: 0.05 });
            observer.observe(heroBanner);
        }
    });
</script>
