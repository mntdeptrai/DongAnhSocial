<div class="admin-table-container">
    <table class="admin-data-table">
        <thead>
            <tr>
                <th style="width: 70px; text-align: center;">Hình Ảnh</th>
                <th>Tên Gian Hàng & Hộ Kinh Doanh</th>
                <th>SĐT Chủ Hộ & Ngân Hàng</th>
                <th>Sản Phẩm & Đơn Giá Kinh Doanh</th>
                <th style="text-align: center;">Trạng Thái ATTP & QR</th>
                <th style="text-align: center; width: 140px;">Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            @if($stalls->count() > 0)
                @foreach($stalls as $st)
                    <tr>
                        <td style="text-align: center;">
                            <img src="{{ $st->image_path ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=150&q=80' }}" style="width: 52px; height: 52px; object-fit: cover; border-radius: 12px; border: 1.5px solid var(--admin-border);" alt="{{ $st->stall_name }}">
                        </td>
                        <td>
                            <strong style="color: #0284c7; font-size: 0.94rem; display: flex; align-items: center; gap: 4px;">
                                🛒 {{ $st->stall_name }}
                            </strong>
                            <span style="font-size: 0.81rem; color: var(--admin-text-main); display: block; margin-top: 3px;">
                                👤 Chủ hộ: <strong>{{ $st->seller_name ?: 'Chưa cập nhật' }}</strong>
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 0.84rem; color: var(--admin-text-main); font-weight: 600; display: block;">
                                📞 {{ $st->seller_phone ?: 'Chưa cập nhật' }}
                            </span>
                            @if(!empty($st->bank_account) && $st->bank_account !== '0987654321')
                                <span style="font-size: 0.76rem; color: #0284c7; font-weight: 700; display: block; margin-top: 3px;">
                                    🏦 {{ !empty($st->bank_name) ? $st->bank_name : 'Ngân hàng' }}: {{ $st->bank_account }}
                                </span>
                            @else
                                <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic; display: block; margin-top: 3px;">
                                    🏦 Chưa cập nhật TK ngân hàng
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 5px; max-width: 360px;">
                                @if(isset($st->products) && count($st->products) > 0)
                                    @foreach($st->products as $p)
                                        <div style="font-size: 0.8rem; background: #f8fafc; padding: 4px 10px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-weight: 600; color: #1e293b;">🏷️ {{ $p->name }}</span>
                                            @if($p->price)
                                                <span style="color: #ea580c; font-weight: 700; font-size: 0.78rem; margin-left: 8px; white-space: nowrap;">
                                                    💰 {{ is_numeric($p->price) ? number_format($p->price, 0, ',', '.') . 'đ' : $p->price }}{{ $p->unit ? '/' . $p->unit : '' }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span style="font-size: 0.78rem; color: #94a3b8; font-style: italic;">Chưa có sản phẩm</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                <span class="admin-badge admin-badge-success" style="font-size: 0.7rem; font-weight: 700;">
                                    ✓ ATTP
                                </span>
                                @if(!empty($st->bank_account))
                                    <span class="admin-badge admin-badge-primary" style="font-size: 0.7rem; font-weight: 700; background: #e0f2fe; color: #0369a1;">
                                        💳 CÓ QR
                                    </span>
                                @else
                                    <span class="admin-badge" style="font-size: 0.7rem; font-weight: 600; background: #f1f5f9; color: #64748b;">
                                        💳 CHƯA QR
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                <a href="/admin/stalls/{{ $st->id }}/edit" class="btn-admin btn-admin-primary" style="padding: 6px 10px; font-size: 0.72rem; font-weight: 700; border-radius: 6px;" title="Chỉnh sửa gian hàng">
                                    ✏️ Sửa
                                </a>
                                <form action="/admin/stalls/{{ $st->id }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa gian hàng này khỏi hệ thống?')" style="display: inline; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-admin btn-admin-danger" style="padding: 6px 10px; font-size: 0.72rem; font-weight: 700; border-radius: 6px;" title="Xóa gian hàng">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px 0; color: var(--admin-text-muted); font-style: italic;">
                        🔍 Không tìm thấy gian hàng nào khớp với tìm kiếm.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div style="margin-top: 16px;">
    {{ $stalls->links() }}
</div>
