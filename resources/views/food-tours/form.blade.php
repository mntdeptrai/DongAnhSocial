@extends('layouts.food-tour')

@section('title', isset($tour) ? 'Chỉnh sửa Lộ trình - DongAnh Food Tour' : 'Tạo Lộ trình mới - DongAnh Food Tour')

@section('styles')
<style>
    .form-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .form-glass-card {
        background: rgba(18, 18, 24, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 126, 41, 0.15);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    }
    
    [data-theme="light"] .form-glass-card {
        background: rgba(255, 255, 255, 0.85);
        border-color: rgba(255, 126, 41, 0.15);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
    }

    .form-title {
        font-family: var(--font-heading);
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 30px;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-align: center;
    }

    .form-section-title {
        font-family: var(--font-heading);
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 30px 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px dashed rgba(255, 126, 41, 0.2);
        padding-bottom: 8px;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-label {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .form-input, .form-select, .form-textarea {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 12px 16px;
        color: var(--text-main);
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
        outline: none;
        width: 100%;
        box-sizing: border-box;
    }

    [data-theme="light"] .form-input, 
    [data-theme="light"] .form-select, 
    [data-theme="light"] .form-textarea {
        background: rgba(15, 23, 42, 0.02);
        border-color: rgba(15, 23, 42, 0.08);
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: var(--primary);
        background: rgba(255, 255, 255, 0.05);
        box-shadow: 0 0 12px rgba(255, 126, 41, 0.15);
    }

    [data-theme="light"] .form-input:focus, 
    [data-theme="light"] .form-select:focus, 
    [data-theme="light"] .form-textarea:focus {
        background: rgba(255, 255, 255, 1);
        box-shadow: 0 0 12px rgba(255, 126, 41, 0.1);
    }

    .eatery-search-container {
        position: relative;
        margin-bottom: 25px;
    }

    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: rgba(26, 26, 38, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 126, 41, 0.2);
        border-radius: 12px;
        z-index: 100;
        max-height: 250px;
        overflow-y: auto;
        margin-top: 5px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    }

    [data-theme="light"] .search-results-dropdown {
        background: rgba(255, 255, 255, 0.98);
        border-color: rgba(15, 23, 42, 0.1);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    }

    .search-result-item {
        padding: 12px 16px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        transition: background 0.2s;
    }

    [data-theme="light"] .search-result-item {
        border-bottom-color: rgba(0,0,0,0.03);
    }

    .search-result-item:hover {
        background: rgba(255, 126, 41, 0.08);
    }

    .stop-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1.5px solid rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 15px;
        position: relative;
        transition: all 0.3s ease;
    }

    [data-theme="light"] .stop-card {
        background: rgba(0, 0, 0, 0.01);
        border-color: rgba(0, 0, 0, 0.05);
    }

    .stop-card:hover {
        border-color: rgba(255, 126, 41, 0.25);
        background: rgba(255, 255, 255, 0.03);
    }

    .stop-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .stop-index-badge {
        background: var(--primary-grad);
        color: #fff;
        font-weight: 800;
        font-size: 0.8rem;
        padding: 4px 12px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .stop-actions {
        display: flex;
        gap: 6px;
    }

    .btn-icon {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-icon:hover {
        background: rgba(255, 126, 41, 0.15);
        border-color: var(--primary);
        color: var(--primary);
    }

    .btn-icon.btn-delete:hover {
        background: rgba(239, 68, 68, 0.15);
        border-color: #ef4444;
        color: #ef4444;
    }

    .btn-submit-tour {
        background: var(--primary-grad);
        color: #fff;
        border: none;
        padding: 16px 40px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 1.05rem;
        cursor: pointer;
        width: 100%;
        margin-top: 30px;
        box-shadow: var(--shadow-glow);
        transition: all 0.3s ease;
    }

    .btn-submit-tour:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(255, 126, 41, 0.4);
    }

    /* Interactive Preset Selection Pills */
    .option-pills-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 6px;
    }

    .option-pill-btn {
        background: rgba(15, 23, 42, 0.03);
        border: 1.5px solid rgba(15, 23, 42, 0.12);
        border-radius: 14px;
        padding: 10px 18px;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-main);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        user-select: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .option-pill-btn:hover {
        border-color: #0ea5e9;
        background: rgba(14, 165, 233, 0.08);
        color: #0ea5e9;
        transform: translateY(-1px);
    }

    .option-pill-btn.active {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border-color: #0ea5e9;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.35);
        transform: translateY(-1px);
    }

    [data-theme="dark"] .option-pill-btn {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.15);
        color: #ffffff;
    }
</style>
@endsection

@section('content')
<div class="form-container" x-data="tourForm()">
    <div class="form-glass-card">
        <h1 class="form-title">
            {{ isset($tour) ? '✏️ Chỉnh sửa Lộ trình' : '🗺️ Tự thiết kế Lộ trình' }}
        </h1>
        
        <form action="{{ isset($tour) ? route('food-tours.update', $tour->slug) : route('food-tours.store') }}" method="POST" enctype="multipart/form-data" @submit="validateForm($event)">
            @csrf
            @if(isset($tour))
                @method('PUT')
            @endif

            <!-- Thống báo lỗi nếu có validation errors -->
            @if ($errors->any())
                <div style="background: rgba(239, 68, 68, 0.1); border: 1.5px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 25px;">
                    <strong style="color: #ef4444; display: block; margin-bottom: 8px;">⚠️ Lỗi biểu mẫu:</strong>
                    <ul style="margin: 0; padding-left: 20px; color: #f87171; font-size: 0.88rem; line-height: 1.5;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-section-title">
                <span>📝</span> Thông tin chung Lộ trình
            </div>

            <div class="form-group">
                <label class="form-label">Tên lộ trình <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="Ví dụ: Hành trình Phố ẩm thực Cao Lỗ về đêm" value="{{ old('name', $tour->name ?? '') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả ngắn gọn <span style="color:#ef4444;">*</span></label>
                <textarea name="description" rows="3" class="form-textarea" placeholder="Mô tả khoảng 2-3 câu ngắn về lộ trình này." required>{{ old('description', $tour->description ?? '') }}</textarea>
            </div>

            <!-- 1. Thời lượng & Khoảng cách di chuyển -->
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">⏱️ Thời lượng ước tính <span style="color:#ef4444;">*</span></label>
                    <select x-model="durationSelect" @change="if(durationSelect !== 'custom') duration = durationSelect; else duration = ''" class="form-select">
                        <option value="1.5 giờ">1.5 giờ (Nhanh gọn)</option>
                        <option value="2.0 giờ">2.0 giờ (Phổ thông)</option>
                        <option value="2.5 giờ">2.5 giờ (Tiêu chuẩn)</option>
                        <option value="3.0 giờ">3.0 giờ (Thong thả)</option>
                        <option value="Nửa ngày (4h)">Nửa ngày (4 tiếng)</option>
                        <option value="custom">✏️ Nhập thời lượng khác...</option>
                    </select>
                    <input type="hidden" name="duration" :value="duration" x-if="durationSelect !== 'custom'">
                    <input type="text" name="duration" x-model="duration" class="form-input" style="margin-top: 8px;" x-show="durationSelect === 'custom'" placeholder="Ví dụ: 3.5 giờ">
                </div>

                <div class="form-group">
                    <label class="form-label">📏 Khoảng cách di chuyển <span style="color:#ef4444;">*</span></label>
                    <select x-model="distanceSelect" @change="if(distanceSelect !== 'custom') distance = distanceSelect; else distance = ''" class="form-select">
                        <option value="2.0 km">2.0 km (Rất gần)</option>
                        <option value="3.5 km">3.5 km (Gần)</option>
                        <option value="5.0 km">5.0 km (Vừa phải)</option>
                        <option value="7.5 km">7.5 km (Khá xa)</option>
                        <option value="10.0 km">10.0 km (Xa)</option>
                        <option value="custom">✏️ Nhập khoảng cách khác...</option>
                    </select>
                    <input type="hidden" name="distance" :value="distance" x-if="distanceSelect !== 'custom'">
                    <input type="text" name="distance" x-model="distance" class="form-input" style="margin-top: 8px;" x-show="distanceSelect === 'custom'" placeholder="Ví dụ: 4.5 km">
                </div>
            </div>

            <!-- 2. Ngân sách & Khung giờ đẹp nhất -->
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">💰 Ngân sách dự chi <span style="color:#ef4444;">*</span></label>
                    <select x-model="budgetSelect" @change="if(budgetSelect !== 'custom') budget = budgetSelect; else budget = ''" class="form-select">
                        <option value="100.000đ">100.000đ (Tiết kiệm)</option>
                        <option value="150.000đ">150.000đ (Phổ thông)</option>
                        <option value="200.000đ">200.000đ (Tiêu chuẩn)</option>
                        <option value="300.000đ">300.000đ (Thoải mái)</option>
                        <option value="500.000đ">500.000đ (Sang xịn)</option>
                        <option value="custom">✏️ Nhập ngân sách khác...</option>
                    </select>
                    <input type="hidden" name="budget" :value="budget" x-if="budgetSelect !== 'custom'">
                    <input type="text" name="budget" x-model="budget" class="form-input" style="margin-top: 8px;" x-show="budgetSelect === 'custom'" placeholder="Ví dụ: 150.000đ - 250.000đ" @input="formatBudgetInput($event)">
                </div>

                <div class="form-group">
                    <label class="form-label">🕒 Khung giờ đẹp nhất <span style="color:#ef4444;">*</span></label>
                    <select x-model="bestTimeSelect" @change="if(bestTimeSelect !== 'custom') best_time = bestTimeSelect; else best_time = ''" class="form-select">
                        <option value="🌅 Sáng (07:00 - 11:00)">🌅 Sáng (07:00 - 11:00)</option>
                        <option value="☀️ Trưa (11:00 - 14:00)">☀️ Trưa (11:00 - 14:00)</option>
                        <option value="🌇 Chiều (14:30 - 17:30)">🌇 Chiều (14:30 - 17:30)</option>
                        <option value="🌙 Tối (18:00 - 22:00)">🌙 Tối (18:00 - 22:00)</option>
                        <option value="🌃 Đêm khuya (22:00 - 01:00)">🌃 Đêm khuya (22:00 - 01:00)</option>
                        <option value="custom">✏️ Nhập khung giờ khác...</option>
                    </select>
                    <input type="hidden" name="best_time" :value="best_time" x-if="bestTimeSelect !== 'custom'">
                    <input type="text" name="best_time" x-model="best_time" class="form-input" style="margin-top: 8px;" x-show="bestTimeSelect === 'custom'" placeholder="Ví dụ: 17:00 - 21:00">
                </div>
            </div>

            <!-- 3. Phong cách & Cấp độ trải nghiệm -->
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">✨ Chủ đề & Phong cách Lộ trình</label>
                    <select name="mood" x-model="mood" class="form-select">
                        <option value="specialty">🌿 Đặc sản Đông Anh</option>
                        <option value="chill">☕ Chill cuối tuần</option>
                        <option value="night">🌙 Ăn đêm Cao Lỗ</option>
                        <option value="cheap">🎓 Sinh viên giá rẻ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">⚡ Cấp độ trải nghiệm</label>
                    <select name="difficulty" x-model="difficulty" class="form-select">
                        <option value="☕ Nhẹ nhàng">☕ Nhẹ nhàng</option>
                        <option value="🏃 Sôi động">🏃 Sôi động</option>
                        <option value="🔥 Thử thách">🔥 Thử thách</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">🖼️ Hình ảnh nền Lộ trình (Thumbnail)</label>
                
                <!-- Tab Mode Toggle: File Upload vs Image Link -->
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <button type="button" @click="imgMode = 'file'" :style="imgMode === 'file' ? 'background: #0ea5e9; color: #fff; border-color: #0ea5e9;' : 'background: #f1f5f9; color: #475569; border-color: #e2e8f0;'" style="padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 700; border: 1px solid; cursor: pointer; transition: all 0.2s;">
                        📁 Tải ảnh từ máy tính / Điện thoại
                    </button>
                    <button type="button" @click="imgMode = 'url'" :style="imgMode === 'url' ? 'background: #0ea5e9; color: #fff; border-color: #0ea5e9;' : 'background: #f1f5f9; color: #475569; border-color: #e2e8f0;'" style="padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 700; border: 1px solid; cursor: pointer; transition: all 0.2s;">
                        🔗 Dán Link URL ảnh
                    </button>
                </div>

                <!-- Input File Upload -->
                <div x-show="imgMode === 'file'" style="background: rgba(14, 165, 233, 0.04); border: 2px dashed rgba(14, 165, 233, 0.3); border-radius: 16px; padding: 20px; text-align: center; cursor: pointer;" @click="$refs.fileInput.click()">
                    <span style="font-size: 1.8rem; display: block; margin-bottom: 6px;">📸</span>
                    <strong style="color: #0f172a; font-size: 0.9rem; display: block;">Bấm vào đây để chọn tập tin ảnh từ thiết bị</strong>
                    <span style="font-size: 0.75rem; color: #64748b;">Hỗ trợ định dạng JPG, PNG, WEBP, GIF (Tối đa 5MB)</span>
                    <input type="file" ref="fileInput" name="thumbnail_file" accept="image/*" style="display: none;" @change="handleFilePreview($event)">
                </div>

                <!-- Input URL Link -->
                <div x-show="imgMode === 'url'">
                    <input type="url" name="thumbnail" x-model="imageUrl" class="form-input" placeholder="Nhập liên kết ảnh URL (Ví dụ: https://images.unsplash.com/...)" value="{{ old('thumbnail', $tour->thumbnail ?? '') }}">
                </div>

                <!-- Image Live Preview Box -->
                <div x-show="previewUrl || imageUrl" style="margin-top: 12px; position: relative; height: 180px; border-radius: 14px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                    <img :src="previewUrl || imageUrl" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80';">
                    <span style="position: absolute; bottom: 8px; right: 8px; background: rgba(15, 23, 42, 0.8); color: #fff; font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; font-weight: 700;">Xem trước ảnh nền</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">📖 Câu chuyện hành trình (Lời tự sự dẫn dắt)</label>
                <textarea name="story" rows="4" class="form-textarea" placeholder="Bộc lộ cảm xúc, kể câu chuyện vì sao bạn kết nối các địa điểm này lại với nhau...">{{ old('story', $tour->story ?? '') }}</textarea>
            </div>

            <!-- CHẶNG DỪNG CHÂN (TIMELINE BUILDER) -->
            <div class="form-section-title">
                <span>📍</span> Tiến trình Lộ trình (<span x-text="stops.length"></span> chặng đã chọn)
            </div>

            <!-- Timeline Stops Container -->
            <div style="position: relative; padding-left: 24px; border-left: 3px dashed #0ea5e9; margin-left: 14px; margin-bottom: 30px;">
                
                <!-- Loop through added stops -->
                <template x-for="(stop, index) in stops" :key="index">
                    <div style="position: relative; margin-bottom: 24px;">
                        
                        <!-- Timeline Step Node Badge -->
                        <div style="position: absolute; left: -39px; top: 18px; width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; box-shadow: 0 0 10px rgba(14, 165, 233, 0.4); z-index: 2;">
                            <span x-text="index + 1"></span>
                        </div>

                        <!-- Card Content -->
                        <div class="stop-card" style="margin-bottom: 0; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 18px; padding: 20px; box-shadow: 0 4px 14px rgba(15,23,42,0.04);">
                            <div class="stop-header">
                                <div class="stop-index-badge" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); font-size: 0.78rem; padding: 4px 12px; border-radius: 20px;">
                                    <span>📍 Chặng <span x-text="index + 1"></span></span>
                                    <span style="opacity:0.85; font-size: 0.75rem;" x-text="'(' + stop.category_name + ')'"></span>
                                </div>
                                <div class="stop-actions">
                                    <button type="button" @click="moveStop(index, -1)" class="btn-icon" :disabled="index === 0" title="Di chuyển lên" style="border-radius: 8px;">▲</button>
                                    <button type="button" @click="moveStop(index, 1)" class="btn-icon" :disabled="index === stops.length - 1" title="Di chuyển xuống" style="border-radius: 8px;">▼</button>
                                    <button type="button" @click="removeStop(index)" class="btn-icon btn-delete" title="Xóa chặng này" style="border-radius: 8px;">✕</button>
                                </div>
                            </div>

                            <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <span>🍜</span> <span x-text="stop.name"></span>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" :name="'stops['+index+'][eatery_id]'" :value="stop.eatery_id">

                            <div class="grid-2">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label" style="font-size:0.8rem; color: #475569;">⏱️ Thời gian trải nghiệm gợi ý</label>
                                    <div style="display: flex; gap: 6px; margin-bottom: 6px; flex-wrap: wrap;">
                                        <template x-for="tOpt in ['30 phút', '45 phút', '60 phút', '90 phút']">
                                            <button type="button" @click="stop.estimated_time = tOpt" :class="{'active': stop.estimated_time === tOpt}" class="option-pill-btn" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 8px;">
                                                <span x-text="tOpt"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <input type="text" :name="'stops['+index+'][estimated_time]'" x-model="stop.estimated_time" class="form-input" style="padding: 8px 12px; font-size:0.88rem;" placeholder="Ví dụ: 45 phút">
                                </div>
                                
                                <div class="form-group" style="margin-bottom:0;">
                                    <label class="form-label" style="font-size:0.8rem; color: #475569;">💡 Gợi ý thực đơn / Mẹo hay tại đây</label>
                                    <input type="text" :name="'stops['+index+'][stop_story]'" x-model="stop.stop_story" class="form-input" style="padding: 8px 12px; font-size:0.88rem;" placeholder="Ví dụ: Thử món nổi tiếng nhất, đi tầm chiều mát...">
                                </div>
                            </div>
                        </div>

                        <!-- Connector arrow line -->
                        <div x-show="index < stops.length - 1" style="text-align: center; margin: 10px 0 -4px 0; color: #0ea5e9; font-size: 0.8rem; font-weight: 700;">
                            ↓ Di chuyển sang Chặng <span x-text="index + 2"></span>
                        </div>
                    </div>
                </template>

                <!-- INLINE SEARCH CARD FOR NEXT STOP -->
                <div style="position: relative; margin-top: 10px;">
                    <div style="position: absolute; left: -39px; top: 18px; width: 28px; height: 28px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; z-index: 2;">
                        <span x-text="stops.length + 1"></span>
                    </div>

                    <div class="eatery-search-container" style="background: rgba(14, 165, 233, 0.04); border: 2px dashed #0ea5e9; border-radius: 18px; padding: 20px;">
                        <label class="form-label" style="margin-bottom: 8px; display:flex; align-items: center; gap: 8px; color: #0284c7; font-size: 0.95rem; font-weight: 800;">
                            <span>➕ Chọn địa điểm cho Chặng <span x-text="stops.length + 1"></span>:</span>
                        </label>
                        
                        <input type="text" 
                               ref="searchInput" 
                               x-model="searchQuery" 
                               @input="filterEateries()" 
                               @focus="dropdownOpen = true" 
                               class="form-input" 
                               placeholder="🔍 Tìm tên quán ăn, nhà hàng, địa điểm Đông Anh..."
                               style="background: #ffffff; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                        
                        <!-- Dropdown Results -->
                        <div class="search-results-dropdown" x-show="dropdownOpen && filteredEateries.length > 0" style="position: relative; top: auto; margin-top: 10px; max-height: 280px;">
                            <template x-for="eat in filteredEateries" :key="eat.id">
                                <div class="search-result-item" @click="addStop(eat)" style="padding: 12px 16px;">
                                    <div>
                                        <strong style="color: #0f172a; font-size: 0.92rem;" x-text="eat.name"></strong>
                                        <span style="font-size: 0.75rem; color: #64748b; display: block; margin-top: 2px;" x-text="eat.address"></span>
                                    </div>
                                    <span style="font-size: 0.7rem; background: #e0f2fe; color: #0284c7; padding: 3px 10px; border-radius: 20px; font-weight: 800; white-space: nowrap;" x-text="eat.category_name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Submit button -->
            <button type="submit" class="btn-submit-tour">
                {{ isset($tour) ? '💾 Lưu thay đổi lộ trình' : '🚀 Xuất bản Lộ trình' }}
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Toàn bộ danh sách eateries có sẵn
    const allEateries = {!! json_encode($eateries->map(function($e) {
        return [
            'id' => $e->id,
            'name' => $e->name,
            'address' => $e->address,
            'category_name' => $e->category->name ?? 'Món ăn & Du lịch',
        ];
    })) !!};

    function tourForm() {
        return {
            duration: "{{ old('duration', $tour->duration ?? '2.5 giờ') }}",
            durationSelect: "{{ old('duration', $tour->duration ?? '2.5 giờ') }}",

            distance: "{{ old('distance', $tour->distance ?? '5.0 km') }}",
            distanceSelect: "{{ old('distance', $tour->distance ?? '5.0 km') }}",

            budget: "{{ old('budget', $tour->budget ?? '200.000đ') }}",
            budgetSelect: "{{ old('budget', $tour->budget ?? '200.000đ') }}",

            best_time: "{{ old('best_time', $tour->best_time ?? '🌙 Tối (18:00 - 22:00)') }}",
            bestTimeSelect: "{{ old('best_time', $tour->best_time ?? '🌙 Tối (18:00 - 22:00)') }}",

            mood: "{{ old('mood', $tour->mood ?? 'specialty') }}",
            difficulty: "{{ old('difficulty', $tour->difficulty ?? '☕ Nhẹ nhàng') }}",

            imgMode: 'file',
            previewUrl: null,
            imageUrl: "{{ old('thumbnail', $tour->thumbnail ?? '') }}",

            handleFilePreview(event) {
                const file = event.target.files[0];
                if (file) {
                    this.previewUrl = URL.createObjectURL(file);
                }
            },

            stops: {!! json_encode(isset($tour) ? $tour->stops->map(function($s) {
                return [
                    'eatery_id' => $s->eatery_id,
                    'name' => $s->eatery->name,
                    'category_name' => $s->eatery->category->name ?? 'Du lịch & Ẩm thực',
                    'estimated_time' => $s->estimated_time ?: '45 phút',
                    'stop_story' => $s->stop_story ?: '',
                ];
            }) : []) !!},
            searchQuery: '',
            filteredEateries: [],
            dropdownOpen: false,

            formatBudgetInput(event) {
                let input = event.target;
                let val = input.value;
                if (!val) return;
                
                // Nếu ký tự cuối cùng là dấu gạch ngang hoặc khoảng trắng thì không format để người dùng có thể gõ tiếp khoảng giá (ví dụ: - )
                if (val.endsWith('-') || val.endsWith(' ') || val.endsWith('–') || val.endsWith('đ')) {
                    if (val === 'đ') {
                        input.value = '';
                        return;
                    }
                }
                
                let parts = val.split(/[-–—]/);
                let formatted = "";
                if (parts.length > 1) {
                    formatted = parts.map(part => {
                        let digits = part.replace(/\D/g, "");
                        if (!digits) return "";
                        return new Intl.NumberFormat('vi-VN').format(digits) + "đ";
                    }).join(" - ");
                } else {
                    let digits = val.replace(/\D/g, "");
                    if (!digits) {
                        formatted = "";
                    } else {
                        formatted = new Intl.NumberFormat('vi-VN').format(digits) + "đ";
                    }
                }
                input.value = formatted;
            },

            init() {
                this.filteredEateries = allEateries.slice(0, 10);

                const dOpts = ['1.5 giờ', '2.0 giờ', '2.5 giờ', '3.0 giờ', 'Nửa ngày (4h)'];
                if (!dOpts.includes(this.duration)) this.durationSelect = 'custom';

                const distOpts = ['2.0 km', '3.5 km', '5.0 km', '7.5 km', '10.0 km'];
                if (!distOpts.includes(this.distance)) this.distanceSelect = 'custom';

                const bOpts = ['100.000đ', '150.000đ', '200.000đ', '300.000đ', '500.000đ'];
                if (!bOpts.includes(this.budget)) this.budgetSelect = 'custom';

                const tOpts = ['🌅 Sáng (07:00 - 11:00)', '☀️ Trưa (11:00 - 14:00)', '🌇 Chiều (14:30 - 17:30)', '🌙 Tối (18:00 - 22:00)', '🌃 Đêm khuya (22:00 - 01:00)'];
                if (!tOpts.includes(this.best_time)) this.bestTimeSelect = 'custom';
            },

            focusAddStop() {
                const searchInput = this.$refs.searchInput;
                if (searchInput) {
                    searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        searchInput.focus();
                        this.dropdownOpen = true;
                    }, 250);
                }
            },

            filterEateries() {
                const query = this.searchQuery.toLowerCase().trim();
                const existingIds = this.stops.map(s => Number(s.eatery_id));
                
                // Loại bỏ hoàn toàn các địa điểm đã có trong chặng dừng
                let available = allEateries.filter(eat => !existingIds.includes(Number(eat.id)));
                
                if (!query) {
                    this.filteredEateries = available.slice(0, 10);
                    return;
                }
                this.filteredEateries = available.filter(eat => 
                    eat.name.toLowerCase().includes(query) || 
                    eat.address.toLowerCase().includes(query)
                ).slice(0, 10);
            },

            addStop(eatery) {
                // Check if eatery already added
                const exists = this.stops.some(stop => Number(stop.eatery_id) === Number(eatery.id));
                if (exists) {
                    showCustomAlert('Địa điểm đã chọn', 'Địa điểm này đã có trong lộ trình của bạn!', 'Đã hiểu', null, '⚠️');
                    return;
                }
                
                this.stops.push({
                    eatery_id: eatery.id,
                    name: eatery.name,
                    category_name: eatery.category_name,
                    estimated_time: '45 phút',
                    stop_story: ''
                });

                this.searchQuery = '';
                this.dropdownOpen = false;
                this.filterEateries();
            },

            removeStop(index) {
                this.stops.splice(index, 1);
                this.filterEateries();
            },

            moveStop(index, direction) {
                const targetIndex = index + direction;
                if (targetIndex < 0 || targetIndex >= this.stops.length) return;
                
                // Swap stops
                const temp = this.stops[index];
                this.stops[index] = this.stops[targetIndex];
                this.stops[targetIndex] = temp;
            },

            validateForm(event) {
                if (this.stops.length === 0) {
                    showCustomAlert('Cần thêm địa điểm', 'Bạn cần chọn ít nhất 1 địa điểm chặng dừng chân cho Food Tour trước khi xuất bản!', 'Đã hiểu', null, '🗺️');
                    event.preventDefault();
                    return false;
                }
                return true;
            }
        };
    }
</script>
@endsection
