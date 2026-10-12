<div id="exp-catalog" class="filter-toolbar">
    <div class="filter-tabs">
        <button type="button" class="tab-btn active" data-tab="all">
            <span>🌟 Tất cả</span>
            <span class="tab-count">{{ $tours->count() + $activities->count() }}</span>
        </button>
        <button type="button" class="tab-btn" data-tab="homestay">
            <span>🏡 Ăn tối nhà dân</span>
        </button>
        <button type="button" class="tab-btn" data-tab="farming">
            <span>🌱 Trồng rau sạch</span>
        </button>
        <button type="button" class="tab-btn" data-tab="game">
            <span>🏹 Game & Bắn nỏ</span>
        </button>
        <button type="button" class="tab-btn" data-tab="craft">
            <span>🏺 Làng nghề & Bún</span>
        </button>
        <button type="button" class="tab-btn" data-tab="monument">
            <span>🎫 Vé & Di tích</span>
        </button>
    </div>

    {{-- Live Search Box --}}
    <div class="filter-search-box">
        <span class="filter-search-icon">🔍</span>
        <input type="text" id="exp-search-input" class="filter-search-input" placeholder="Tìm ăn tối, trồng rau, mật mã, bún...">
    </div>
</div>
