<?php $__env->startSection('title', isset($tour) ? 'Chỉnh sửa Lộ trình - DongAnh Food Tour' : 'Tạo Lộ trình mới - DongAnh Food Tour'); ?>

<?php $__env->startSection('styles'); ?>
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
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="form-container" x-data="tourForm()">
    <div class="form-glass-card">
        <h1 class="form-title">
            <?php echo e(isset($tour) ? '✏️ Chỉnh sửa Lộ trình' : '🗺️ Tự thiết kế Lộ trình'); ?>

        </h1>
        
        <form action="<?php echo e(isset($tour) ? route('food-tours.update', $tour->slug) : route('food-tours.store')); ?>" method="POST" @submit="validateForm($event)">
            <?php echo csrf_field(); ?>
            <?php if(isset($tour)): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            <!-- Thống báo lỗi nếu có validation errors -->
            <?php if($errors->any()): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1.5px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 25px;">
                    <strong style="color: #ef4444; display: block; margin-bottom: 8px;">⚠️ Lỗi biểu mẫu:</strong>
                    <ul style="margin: 0; padding-left: 20px; color: #f87171; font-size: 0.88rem; line-height: 1.5;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-section-title">
                <span>📝</span> Thông tin chung Lộ trình
            </div>

            <div class="form-group">
                <label class="form-label">Tên lộ trình <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="Ví dụ: Hành trình Phố ẩm thực Cao Lỗ về đêm" value="<?php echo e(old('name', $tour->name ?? '')); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả ngắn gọn <span style="color:#ef4444;">*</span></label>
                <textarea name="description" rows="3" class="form-textarea" placeholder="Mô tả khoảng 2-3 câu ngắn về lộ trình này." required><?php echo e(old('description', $tour->description ?? '')); ?></textarea>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Thời lượng ước tính <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="duration" class="form-input" placeholder="Ví dụ: 2.5 giờ" value="<?php echo e(old('duration', $tour->duration ?? '2.5 giờ')); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Khoảng cách di chuyển <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="distance" class="form-input" placeholder="Ví dụ: 4.5 km" value="<?php echo e(old('distance', $tour->distance ?? '5.0 km')); ?>" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Ngân sách dự chi <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="budget" class="form-input" placeholder="Ví dụ: 150.000đ - 250.000đ" value="<?php echo e(old('budget', $tour->budget ?? '200.000đ')); ?>" required @input="formatBudgetInput($event)" @blur="formatBudgetInput($event)">
                </div>

                <div class="form-group">
                    <label class="form-label">Khung giờ đẹp nhất <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="best_time" class="form-input" placeholder="Ví dụ: 17:00 - 21:00" value="<?php echo e(old('best_time', $tour->best_time ?? '18:00 - 22:00')); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Thumbnail ảnh nền (Link ảnh)</label>
                <input type="url" name="thumbnail" class="form-input" placeholder="Nhập liên kết ảnh làm hình nền chính (để trống nếu sử dụng ảnh mặc định)" value="<?php echo e(old('thumbnail', $tour->thumbnail ?? '')); ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Câu chuyện hành trình (Lời tự sự dẫn dắt)</label>
                <textarea name="story" rows="4" class="form-textarea" placeholder="Bộc lộ cảm xúc, kể câu chuyện vì sao bạn kết nối các địa điểm này lại với nhau..."><?php echo e(old('story', $tour->story ?? '')); ?></textarea>
            </div>

            <!-- CHẶNG DỪNG CHÂN (DYNAMIC BUILDER) -->
            <div class="form-section-title">
                <span>📍</span> Các chặng dừng chân (Đã thêm: <span x-text="stops.length"></span>)
            </div>

            <!-- Search Eatery to add -->
            <div class="eatery-search-container">
                <label class="form-label" style="margin-bottom: 6px; display:block;">🔍 Tìm kiếm địa điểm để thêm chặng</label>
                <input type="text" x-model="searchQuery" @input="filterEateries()" @focus="dropdownOpen = true" @click.away="dropdownOpen = false" class="form-input" placeholder="Nhập tên quán ăn, nhà hàng, điểm du lịch Đông Anh...">
                
                <!-- Drodown list -->
                <div class="search-results-dropdown" x-show="dropdownOpen && filteredEateries.length > 0">
                    <template x-for="eat in filteredEateries" :key="eat.id">
                        <div class="search-result-item" @click="addStop(eat)">
                            <div>
                                <strong style="color: var(--text-main); font-size: 0.9rem;" x-text="eat.name"></strong>
                                <span style="font-size: 0.72rem; color: var(--text-muted); display: block; max-width: 500px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="eat.address"></span>
                            </div>
                            <span style="font-size: 0.68rem; background: rgba(255, 126, 41, 0.15); color: var(--primary); padding: 2px 8px; border-radius: 20px; font-weight: 700;" x-text="eat.category_name"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Empty State Stops -->
            <div x-show="stops.length === 0" style="text-align: center; padding: 40px; background: rgba(255,255,255,0.01); border: 1.5px dashed rgba(255,126,41,0.2); border-radius: 16px; margin-bottom: 20px;">
                <span style="font-size: 2.5rem; display: block; margin-bottom: 12px;">🗺️</span>
                <strong style="color: var(--text-main);">Chưa có chặng dừng chân nào được thêm</strong>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Hãy sử dụng ô tìm kiếm ở trên để thêm các địa điểm bạn muốn vào lộ trình (Cần ít nhất 1 địa điểm).</p>
            </div>

            <!-- Stops Cards list -->
            <div style="margin-bottom: 25px;">
                <template x-for="(stop, index) in stops" :key="index">
                    <div class="stop-card">
                        <div class="stop-header">
                            <div class="stop-index-badge">
                                <span x-text="'Chặng ' + (index + 1)"></span>
                                <span style="opacity:0.75; font-size: 0.8rem;" x-text="'(' + stop.category_name + ')'"></span>
                            </div>
                            <div class="stop-actions">
                                <button type="button" @click="moveStop(index, -1)" class="btn-icon" :disabled="index === 0" title="Di chuyển lên">▲</button>
                                <button type="button" @click="moveStop(index, 1)" class="btn-icon" :disabled="index === stops.length - 1" title="Di chuyển xuống">▼</button>
                                <button type="button" @click="removeStop(index)" class="btn-icon btn-delete" title="Xóa chặng">✕</button>
                            </div>
                        </div>

                        <div style="font-weight: 800; font-size: 1rem; color: var(--text-main); margin-bottom: 14px;" x-text="stop.name"></div>

                        <!-- Hidden fields to submit to backend -->
                        <input type="hidden" :name="'stops['+index+'][eatery_id]'" :value="stop.eatery_id">

                        <div class="grid-2">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" style="font-size:0.8rem;">⏱️ Thời gian trải nghiệm gợi ý</label>
                                <input type="text" :name="'stops['+index+'][estimated_time]'" x-model="stop.estimated_time" class="form-input" style="padding: 8px 12px; font-size:0.88rem;" placeholder="Ví dụ: 45 phút">
                            </div>
                            
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" style="font-size:0.8rem;">💡 Gợi ý thực đơn / Mẹo hay</label>
                                <input type="text" :name="'stops['+index+'][stop_story]'" x-model="stop.stop_story" class="form-input" style="padding: 8px 12px; font-size:0.88rem;" placeholder="Ví dụ: Nên gọi đĩa bún sợi nhỏ chấm thêm tương quê ngon...">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn-submit-tour">
                <?php echo e(isset($tour) ? '💾 Lưu thay đổi lộ trình' : '🚀 Xuất bản Lộ trình'); ?>

            </button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    // Toàn bộ danh sách eateries có sẵn
    const allEateries = <?php echo json_encode($eateries->map(function($e) {
        return [
            'id' => $e->id,
            'name' => $e->name,
            'address' => $e->address,
            'category_name' => $e->category->name ?? 'Món ăn & Du lịch',
        ];
    })); ?>;

    function tourForm() {
        return {
            stops: <?php echo json_encode(isset($tour) ? $tour->stops->map(function($s) {
                return [
                    'eatery_id' => $s->eatery_id,
                    'name' => $s->eatery->name,
                    'category_name' => $s->eatery->category->name ?? 'Du lịch & Ẩm thực',
                    'estimated_time' => $s->estimated_time ?: '45 phút',
                    'stop_story' => $s->stop_story ?: '',
                ];
            }) : []); ?>,
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
            },

            filterEateries() {
                const query = this.searchQuery.toLowerCase().trim();
                if (!query) {
                    this.filteredEateries = allEateries.slice(0, 10);
                    return;
                }
                this.filteredEateries = allEateries.filter(eat => 
                    eat.name.toLowerCase().includes(query) || 
                    eat.address.toLowerCase().includes(query)
                ).slice(0, 10);
            },

            addStop(eatery) {
                // Check if eatery already added
                const exists = this.stops.some(stop => stop.eatery_id === eatery.id);
                if (exists) {
                    alert('Địa điểm này đã được thêm vào hành trình rồi!');
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
                this.filteredEateries = allEateries.slice(0, 10);
                this.dropdownOpen = false;
            },

            removeStop(index) {
                this.stops.splice(index, 1);
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
                    alert('Bạn cần thêm ít nhất 1 địa điểm chặng dừng chân cho Food Tour!');
                    event.preventDefault();
                    return false;
                }
                return true;
            }
        };
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.food-tour', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DA_DISCOVERY\resources\views/food-tours/form.blade.php ENDPATH**/ ?>