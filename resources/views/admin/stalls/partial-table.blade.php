<div class="admin-table-container">
    <table class="admin-data-table" style="width: 100%; border-collapse: separate; border-spacing: 0 12px;">
        <thead>
            <tr style="background: #f8fafc; color: #475569; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.03em;">
                <th style="padding: 12px 16px; border-radius: 12px 0 0 12px; width: 70px; text-align: center;">Hình Ảnh</th>
                <th style="padding: 12px 16px;">Tên Gian Hàng & Sản Phẩm Trưng Bày</th>
                <th style="padding: 12px 16px; text-align: center; width: 170px;">Số Lượng Mặt Hàng</th>
                <th style="padding: 12px 16px; text-align: center; width: 180px; border-radius: 0 12px 12px 0;">Thao Tác Gian Hàng</th>
            </tr>
        </thead>
        <tbody>
            @if($stalls->count() > 0)
                @foreach($stalls as $stIndex => $st)
                    @php
                        $stallImg = $st->image_path;
                        if (!empty($stallImg) && str_starts_with(trim($stallImg), '[')) {
                            $decodedImgs = json_decode(trim($stallImg), true);
                            $stallImg = is_array($decodedImgs) && count($decodedImgs) > 0 ? $decodedImgs[0] : '';
                        }
                    @endphp
                    <!-- HÀNG CHÍNH: GIAN HÀNG -->
                    <tr style="background: #ffffff; border: 1.5px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                        <td style="padding: 14px 16px; text-align: center; border-radius: 14px 0 0 14px; background: #ffffff;">
                            <img src="{{ $stallImg ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=150&q=80' }}" style="width: 56px; height: 56px; object-fit: cover; border-radius: 14px; border: 2px solid #0284c7;" alt="{{ $st->stall_name }}">
                        </td>
                        <td style="padding: 14px 16px; background: #ffffff;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <strong style="color: #0369a1; font-size: 1.05rem; letter-spacing: -0.01em;">
                                    🏪 Gian hàng: {{ $st->stall_name }}
                                </strong>
                                <span style="background: #e0f2fe; color: #0369a1; font-size: 0.73rem; font-weight: 800; padding: 3px 10px; border-radius: 20px; border: 1px solid #bae6fd;">
                                    🏛️ Khu trưng bày Chợ Cổ Loa
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px; font-weight: 600;">
                                Danh mục gian hàng: <strong>{{ $st->products_count }}</strong> mặt hàng niêm yết
                            </div>
                        </td>
                        <td style="padding: 14px 16px; text-align: center; background: #ffffff;">
                            <span style="background: #fef3c7; color: #92400e; font-size: 0.88rem; font-weight: 800; padding: 6px 14px; border-radius: 12px; border: 1px solid #fde68a; display: inline-flex; align-items: center; gap: 5px;">
                                📦 {{ $st->products_count }} sản phẩm
                            </span>
                        </td>
                        <td style="padding: 14px 16px; text-align: center; border-radius: 0 14px 14px 0; background: #ffffff;">
                            <div style="display: flex; gap: 8px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                <a href="/admin/stalls/create?stall_name={{ urlencode($st->stall_name) }}" class="btn-admin" style="padding: 8px 14px; font-size: 0.78rem; font-weight: 800; border-radius: 8px; background-color: #10b981; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Thêm món mới vào gian này">
                                    ➕ Thêm món vào gian
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- HÀNG CON: DANH SÁCH CÁC SẢN PHẨM TRONG GIAN HÀNG NÀY -->
                    <tr>
                        <td colspan="4" style="padding: 0 16px 16px 50px;">
                            <div style="background: #ffffff; border: 1.5px dashed #cbd5e1; border-radius: 14px; padding: 16px 20px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                                <div style="font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #0284c7; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                                    <span>📦 Danh sách mặt hàng thuộc gian: <strong>{{ $st->stall_name }}</strong></span>
                                    <span style="color: #64748b; font-weight: 600; text-transform: none;">({{ $st->products_count }} mặt hàng)</span>
                                </div>

                                <table style="width: 100%; border-collapse: collapse;">
                                    <tbody>
                                        @foreach($st->products as $pIndex => $p)
                                            @php
                                                $pImg = $p->image_path;
                                                if (!empty($pImg) && str_starts_with(trim($pImg), '[')) {
                                                    $decoded = json_decode(trim($pImg), true);
                                                    $pImg = is_array($decoded) && count($decoded) > 0 ? $decoded[0] : '';
                                                }
                                            @endphp
                                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='transparent'">
                                                <td style="padding: 10px 8px; width: 48px;">
                                                    <img src="{{ $pImg ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=150&q=80' }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;" alt="{{ $p->name }}">
                                                </td>
                                                <td style="padding: 10px 12px;">
                                                    <strong style="color: #0f172a; font-size: 0.92rem; display: block;">📦 {{ $p->name }}</strong>
                                                    <span style="font-size: 0.76rem; color: #64748b;">Mô tả: {{ $p->description ?: 'Chưa có mô tả' }}</span>
                                                </td>
                                                <td style="padding: 10px 12px; font-weight: 700; color: #ea580c; font-size: 0.88rem; width: 150px;">
                                                    {{ $p->price ? (is_numeric($p->price) ? number_format($p->price, 0, ',', '.') . 'đ' : $p->price) : 'Sản phẩm trưng bày' }}
                                                    @if($p->unit)<span style="font-size: 0.75rem; color: #64748b; font-weight: normal;">/ {{ $p->unit }}</span>@endif
                                                </td>
                                                <td style="padding: 10px 8px; text-align: right; width: 140px;">
                                                    <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                                        <a href="/admin/stalls/{{ $p->id }}/edit" class="btn-admin" style="padding: 6px 11px; font-size: 0.75rem; font-weight: 700; border-radius: 6px; background-color: #0284c7; color: white; text-decoration: none;" title="Chỉnh sửa sản phẩm này">
                                                            ✏️ Sửa
                                                        </a>
                                                        <form action="/admin/stalls/{{ $p->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mặt hàng {{ $p->name }} này?')" style="display: inline; margin: 0;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-admin" style="padding: 6px 11px; font-size: 0.75rem; font-weight: 700; border-radius: 6px; background-color: #ef4444; color: white; border: none; cursor: pointer;" title="Xóa mặt hàng này">
                                                                🗑️ Xóa
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                        📥 Chưa có gian hàng hay sản phẩm trưng bày nào. Bấm <strong>"Thêm Sản Phẩm Mới"</strong> để tạo dữ liệu!
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
