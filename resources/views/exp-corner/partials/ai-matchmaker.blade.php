<div id="ai-matchmaker" class="ai-matchmaker-card">
    <div class="ai-matchmaker-glow"></div>
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <span style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; color: #10b981; display: block; margin-bottom: 4px;">
                🤖 DongAnh AI Assistant
            </span>
            <h3 style="font-size: 1.45rem; font-weight: 900; margin: 0; color: var(--text-main); font-family: var(--font-heading); display: flex; align-items: center; gap: 10px;">
                <span>✨</span> Trợ lý AI Gợi ý Trải nghiệm theo nhu cầu
            </h3>
        </div>
        <span style="font-size: 0.82rem; color: var(--text-muted); background: rgba(16, 185, 129, 0.1); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.25); font-weight: 700;">
            ⚡ Cá nhân hóa 100%
        </span>
    </div>

    <div class="ai-criteria-grid">
        {{-- Tiêu chí 1: Đối tượng --}}
        <div>
            <div class="ai-step-title">
                <span>👥</span> 1. Ai sẽ cùng tham gia?
            </div>
            <div class="ai-chips-group" id="ai-audience-group">
                <span class="ai-chip active" data-val="all">Tất cả</span>
                <span class="ai-chip" data-val="family">👶 Gia đình & Trẻ nhỏ</span>
                <span class="ai-chip" data-val="youth">🎒 Bạn trẻ / Phượt</span>
                <span class="ai-chip" data-val="group">🏛️ Đoàn học sinh / Ngoại khóa</span>
            </div>
        </div>

        {{-- Tiêu chí 2: Thời lượng --}}
        <div>
            <div class="ai-step-title">
                <span>⏳</span> 2. Thời gian bạn có sẵn?
            </div>
            <div class="ai-chips-group" id="ai-duration-group">
                <span class="ai-chip active" data-val="all">Tất cả</span>
                <span class="ai-chip" data-val="half">⚡ Nửa ngày (2 - 3 tiếng)</span>
                <span class="ai-chip" data-val="full">☀️ Trọn vẹn 1 ngày</span>
            </div>
        </div>

        {{-- Tiêu chí 3: Sở thích --}}
        <div>
            <div class="ai-step-title">
                <span>🎯</span> 3. Trọng tâm trải nghiệm?
            </div>
            <div class="ai-chips-group" id="ai-interest-group">
                <span class="ai-chip active" data-val="all">Tất cả</span>
                <span class="ai-chip" data-val="craft">🏺 Nghề thủ công</span>
                <span class="ai-chip" data-val="history">🏹 Bắn nỏ & Di tích Cổ Loa</span>
                <span class="ai-chip" data-val="food">🍜 Tự làm & Ăn uống</span>
            </div>
        </div>
    </div>

    {{-- Nút kích hoạt AI --}}
    <div class="ai-matchmaker-footer">
        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
            💡 <em>Chọn tiêu chí ở trên để AI tự động tinh chỉnh danh sách phù hợp nhất cho chuyến đi của bạn.</em>
        </p>
        <div class="ai-footer-actions">
            <button type="button" id="btn-reset-ai" style="padding: 11px 20px; border-radius: 50px; border: 1px solid rgba(16, 185, 129, 0.3); background: transparent; color: var(--text-muted); font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                🔄 Đặt lại
            </button>
            <button type="button" id="btn-run-ai" style="padding: 11px 26px; border-radius: 50px; border: none; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; font-weight: 800; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); transition: all 0.2s;">
                <span>✨</span> Gợi ý Lộ trình ngay
            </button>
        </div>
    </div>

    {{-- Khung kết quả AI hiển thị động --}}
    <div id="ai-result-container" class="ai-result-box" style="display: none;">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
            <div style="font-size: 2rem; line-height: 1;">💡</div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 6px 0; font-size: 1.05rem; font-weight: 800; color: #10b981; font-family: var(--font-heading);" id="ai-result-title">
                    Gợi ý từ Chuyên gia bản địa Đông Anh
                </h4>
                <p style="margin: 0; font-size: 0.88rem; color: var(--text-main); line-height: 1.6;" id="ai-result-text">
                    Đang phân tích các địa điểm phù hợp...
                </p>
            </div>
        </div>
    </div>
</div>
