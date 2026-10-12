{{-- ========================================================
     AIRBNB-STYLE FULLSCREEN FEEDBACK & DIARY LIBRARY DRAWER
     ======================================================== --}}
<div id="feedbackLibraryModal" class="library-drawer-overlay" style="display: none;">
    <div class="library-drawer-content" role="dialog" aria-modal="true" aria-labelledby="lib-modal-title">
        
        {{-- Drawer Header --}}
        <div class="library-drawer-header">
            <div class="drawer-header-left" style="display: flex; align-items: center; gap: 14px;">
                <button type="button" onclick="closeFeedbackLibraryModal()" class="modal-close-btn" style="position: static; width: 38px; height: 38px; font-size: 1.1rem; flex-shrink: 0;" title="Đóng thư viện">
                    ✕
                </button>
                <div>
                    <h3 id="lib-modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 900; color: var(--text-main); font-family: var(--font-heading); display: flex; align-items: center; gap: 8px;">
                        <span>📖</span> Thư Viện Cảm Nhận & Nhật Ký Trải Nghiệm
                    </h3>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 3px; flex-wrap: wrap;">
                        <span style="font-size: 0.76rem; color: #059669; font-weight: 800; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 2px 8px; border-radius: 12px;">
                            ✓ 1.250+ Đánh giá xác thực
                        </span>
                        <span style="font-size: 0.76rem; color: var(--text-muted);">
                            Cộng đồng trải nghiệm bản địa Đông Anh
                        </span>
                    </div>
                </div>
            </div>

            <div class="drawer-header-actions" style="display: flex; align-items: center; gap: 10px;">
                <button type="button" onclick="openFeedbackModal()" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: none; font-weight: 800; font-size: 0.82rem; cursor: pointer; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);">
                    <span>✍️</span> Viết Cảm Nhận
                </button>
            </div>
        </div>

        {{-- Drawer Body: 2 Columns (Sticky Filter Sidebar + Infinite Feed) --}}
        <div class="library-drawer-body">
            
            {{-- ── COLUMN 1: STICKY SEARCH & FILTER SIDEBAR ── --}}
            <aside style="position: sticky; top: 90px; height: fit-content; display: flex; flex-direction: column; gap: 20px;">
                
                {{-- Scorecard Mini Panel --}}
                <div style="padding: 18px; border-radius: 18px; background: rgba(16, 185, 129, 0.05); border: 1.5px solid rgba(16, 185, 129, 0.2);">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <span style="font-family: var(--font-heading); font-size: 2.4rem; font-weight: 900; color: #10b981; line-height: 1;">
                            4.9
                        </span>
                        <div>
                            <div style="color: #f59e0b; font-size: 0.95rem; font-weight: 800; line-height: 1.2;">
                                ⭐⭐⭐⭐⭐
                            </div>
                            <span style="font-size: 0.74rem; color: var(--text-muted); font-weight: 700;">
                                98% Du khách giới thiệu
                            </span>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.76rem;">
                        <div style="display: flex; justify-content: space-between; color: var(--text-main); font-weight: 600;">
                            <span>Độ nhiệt tình gia chủ/nghệ nhân</span>
                            <span style="color: #10b981; font-weight: 800;">4.9 / 5.0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; color: var(--text-main); font-weight: 600;">
                            <span>Tính xác thực & văn hóa bản địa</span>
                            <span style="color: #10b981; font-weight: 800;">4.9 / 5.0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; color: var(--text-main); font-weight: 600;">
                            <span>Độ an toàn & chu đáo</span>
                            <span style="color: #10b981; font-weight: 800;">5.0 / 5.0</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; color: var(--text-main); font-weight: 600;">
                            <span>Đáng giá tiền (Value for money)</span>
                            <span style="color: #10b981; font-weight: 800;">4.9 / 5.0</span>
                        </div>
                    </div>
                </div>

                {{-- Live Search Input --}}
                <div>
                    <label for="lib-search-input" style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); display: block; margin-bottom: 6px;">
                        🔍 Tìm theo từ khóa
                    </label>
                    <input type="text" id="lib-search-input" placeholder="VD: rau, gà, bé, mật thư, nỏ..." style="width: 100%; padding: 11px 16px; border-radius: 12px; border: 1.5px solid rgba(16, 185, 129, 0.25); font-size: 0.85rem; outline: none; background: var(--bg-card); color: var(--text-main); transition: border-color 0.2s;" oninput="filterLibraryReviews()">
                </div>

                {{-- Filter by Activity --}}
                <div>
                    <label for="lib-activity-select" style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); display: block; margin-bottom: 6px;">
                        🎯 Hoạt động trải nghiệm
                    </label>
                    <select id="lib-activity-select" style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid rgba(16, 185, 129, 0.25); font-size: 0.82rem; outline: none; background: var(--bg-card); color: var(--text-main);" onchange="filterLibraryReviews()">
                        <option value="all">Tất cả hoạt động</option>
                        <option value="Ăn tối tại nhà dân Cổ Loa">🏡 Ăn tối tại nhà dân Cổ Loa</option>
                        <option value="Tour trải nghiệm trồng rau tại Cổ Loa">🌱 Tour trồng rau sinh thái</option>
                        <option value="Game Mật Mã Loa Thành">🏹 Game Mật Mã Loa Thành</option>
                        <option value="Bắn nỏ liên châu Âu Lạc">🎯 Bắn nỏ liên châu Âu Lạc</option>
                        <option value="Tự tay làm bún Mạch Tràng cổ truyền">🍜 Làm bún Mạch Tràng</option>
                        <option value="Tham quan di tích lịch sử Thành Cổ Loa">🏛️ Tham quan di tích Cổ Loa</option>
                    </select>
                </div>

                {{-- Filter by Rating --}}
                <div>
                    <label for="lib-rating-select" style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); display: block; margin-bottom: 6px;">
                        ⭐ Mức đánh giá
                    </label>
                    <select id="lib-rating-select" style="width: 100%; padding: 11px 14px; border-radius: 12px; border: 1.5px solid rgba(16, 185, 129, 0.25); font-size: 0.82rem; outline: none; background: var(--bg-card); color: var(--text-main);" onchange="filterLibraryReviews()">
                        <option value="all">Tất cả mức sao</option>
                        <option value="5">⭐⭐⭐⭐⭐ 5 Sao</option>
                        <option value="4">⭐⭐⭐⭐ 4 Sao</option>
                    </select>
                </div>

                {{-- Checkbox: Only with Photos --}}
                <div>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.82rem; font-weight: 700; color: var(--text-main);">
                        <input type="checkbox" id="lib-has-photo-cb" style="accent-color: #10b981; width: 16px; height: 16px; cursor: pointer;" onchange="filterLibraryReviews()">
                        <span>📸 Chỉ hiển thị bài có ảnh thực tế</span>
                    </label>
                </div>

                {{-- Reset Button --}}
                <button type="button" onclick="resetLibraryFilters()" style="padding: 9px 16px; border-radius: 10px; border: 1px dashed rgba(16, 185, 129, 0.4); background: transparent; color: var(--text-muted); font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease;">
                    🔄 Đặt lại bộ lọc
                </button>

            </aside>

            {{-- ── COLUMN 2: FULL REVIEWS LIST ── --}}
            <main style="display: flex; flex-direction: column;">
                
                {{-- Status Bar --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid rgba(16, 185, 129, 0.15);">
                    <div style="font-size: 0.84rem; color: var(--text-muted); font-weight: 700;">
                        Hiển thị <span id="lib-visible-count" style="color: #10b981; font-weight: 800;">{{ count($feedbacks) }}</span> đánh giá phù hợp
                    </div>
                    <div style="font-size: 0.74rem; color: var(--text-muted);">
                        Sắp xếp: <strong>Mới nhất & Hữu ích nhất</strong>
                    </div>
                </div>

                {{-- Reviews Container --}}
                <div id="lib-feedback-list" style="display: flex; flex-direction: column; gap: 20px;">
                    @foreach($feedbacks as $fb)
                        <article class="lib-review-item" 
                                 data-activity="{{ $fb['activity_name'] }}"
                                 data-rating="{{ $fb['rating'] ?? 5 }}"
                                 data-has-photo="{{ !empty($fb['image_url']) ? '1' : '0' }}"
                                 data-content="{{ Str::lower(($fb['author_name'] ?? '') . ' ' . ($fb['comment'] ?? '') . ' ' . ($fb['activity_name'] ?? '')) }}"
                                 style="padding: 22px; border-radius: 18px; background: var(--bg-card); border: 1.5px solid rgba(16, 185, 129, 0.18); transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
                            
                            {{-- Header --}}
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; text-transform: uppercase; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                                        {{ mb_substr($fb['author_name'] ?? 'DK', 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <strong style="font-size: 0.96rem; color: var(--text-main);">{{ $fb['author_name'] }}</strong>
                                            <span class="verified-badge">✓ Khách đã trải nghiệm</span>
                                        </div>
                                        <span style="font-size: 0.74rem; color: var(--text-muted); display: block; margin-top: 2px;">
                                            {{ $fb['created_at'] ?? 'Mới đây' }}
                                        </span>
                                    </div>
                                </div>

                                <div style="color: #f59e0b; font-size: 0.82rem; font-weight: 800; background: #fffbeb; padding: 4px 10px; border-radius: 12px; border: 1px solid #fef3c7;">
                                    @for($i = 0; $i < ($fb['rating'] ?? 5); $i++)⭐@endfor
                                </div>
                            </div>

                            {{-- Activity badge --}}
                            <div style="margin-bottom: 12px;">
                                <span style="font-size: 0.74rem; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); padding: 4px 12px; border-radius: 20px; font-weight: 700; display: inline-block;">
                                    🎯 {{ $fb['activity_name'] }}
                                </span>
                            </div>

                            {{-- Comment text --}}
                            <p style="margin: 0 0 14px 0; font-size: 0.88rem; color: var(--text-main); line-height: 1.65;">
                                "{{ $fb['comment'] }}"
                            </p>

                            {{-- Attached Photo --}}
                            @if(!empty($fb['image_url']))
                                <div style="position: relative; max-width: 380px; height: 210px; border-radius: 14px; overflow: hidden; border: 1.5px solid rgba(16, 185, 129, 0.25); margin-bottom: 14px;">
                                    <img src="{{ $fb['image_url'] }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Ảnh kỷ niệm du khách" loading="lazy">
                                    <span style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.65); color: #ffffff; padding: 2px 8px; border-radius: 10px; font-size: 0.65rem; font-weight: 700; backdrop-filter: blur(4px);">
                                        📸 Ảnh du khách
                                    </span>
                                </div>
                            @endif

                            {{-- Official Host Reply (if any) --}}
                            @if(!empty($fb['host_reply']))
                                <div class="host-reply-box">
                                    <div style="display: flex; align-items: center; gap: 6px; font-weight: 800; font-size: 0.74rem; color: #10b981; margin-bottom: 3px;">
                                        <span>💬</span>
                                        <span>Phản hồi từ {{ $fb['host_reply']['author'] ?? 'Người tổ chức' }}</span>
                                    </div>
                                    <div>{{ $fb['host_reply']['comment'] }}</div>
                                </div>
                            @endif

                            {{-- Card Footer: Helpful button --}}
                            <div style="margin-top: 14px; display: flex; align-items: center; justify-content: flex-end;">
                                <button type="button" class="btn-helpful" onclick="toggleHelpful(this, '{{ $fb['id'] ?? uniqid() }}')">
                                    <span>👍</span> Hữu ích (<span class="helpful-count">{{ $fb['helpful_count'] ?? 12 }}</span>)
                                </button>
                            </div>

                        </article>
                    @endforeach
                </div>

                {{-- Empty state when no reviews match filters --}}
                <div id="lib-empty-state" style="display: none; text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: 18px; border: 1.5px dashed rgba(16, 185, 129, 0.3); margin-top: 20px;">
                    <span style="font-size: 2.8rem;">🔍</span>
                    <h4 style="margin: 14px 0 6px 0; font-size: 1.1rem; color: var(--text-main); font-weight: 800;">
                        Không tìm thấy đánh giá phù hợp
                    </h4>
                    <p style="margin: 0 0 16px 0; font-size: 0.84rem; color: var(--text-muted);">
                        Thử điều chỉnh từ khóa hoặc xóa bớt tiêu chí lọc để xem thêm cảm nhận khác.
                    </p>
                    <button type="button" onclick="resetLibraryFilters()" style="padding: 10px 22px; border-radius: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: none; font-weight: 700; font-size: 0.84rem; cursor: pointer;">
                        🔄 Hiển thị tất cả đánh giá
                    </button>
                </div>

            </main>

        </div>

    </div>
</div>
