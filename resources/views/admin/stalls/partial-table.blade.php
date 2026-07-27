<div class="admin-table-container">
    <table class="admin-data-table">
        <thead>
            <tr>
                <th style="width: 70px; text-align: center;">Hình Ảnh</th>
                <th>Tên Gian Hàng & Hộ Kinh Doanh</th>
                <th>SĐT Chủ Hộ</th>
                <th>Sản Phẩm & Đơn Giá</th>
                <th style="text-align: center;">Trạng Thái ATTP & QR</th>
                <th style="text-align: center; width: 140px;">Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            @if($stalls->count() > 0)
                @foreach($stalls as $st)
                    <tr>
                        <td style="text-align: center;">
                            <img src="{{ $st->image_path ?: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=150&q=80' }}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 10px; border: 1px solid var(--admin-border);" alt="{{ $st->name }}">
                        </td>
                        <td>
                            <strong style="color: #0284c7; font-size: 0.92rem; display: block;">
                                🛒 {{ $st->stall_name }}
                            </strong>
                            <span style="font-size: 0.8rem; color: var(--admin-text-main); display: block; margin-top: 2px;">
                                👤 Chủ hộ: <strong>{{ $st->seller_name }}</strong>
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 0.84rem; color: var(--admin-text-main); font-weight: 600; display: block;">
                                📞 {{ $st->seller_phone ?: 'Chưa cập nhật' }}
                            </span>
                            @if(!empty($st->bank_account))
                                <span style="font-size: 0.75rem; color: #0284c7; font-weight: 700; display: block; margin-top: 2px;">
                                    🏦 {{ $st->bank_name ?? 'MBBank' }}: {{ $st->bank_account }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong style="font-size: 0.88rem; color: var(--admin-text-main); display: block;">
                                📦 {{ $st->name }}
                            </strong>
                            @if($st->price)
                                <span style="font-size: 0.8rem; color: #ea580c; font-weight: 700; display: inline-block; margin-top: 2px;">
                                    🏷️ {{ $st->price }}{{ $st->unit ? '/' . $st->unit : '' }}
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                <span class="admin-badge admin-badge-success" style="font-size: 0.7rem; font-weight: 700;">
                                    ✓ ATTP
                                </span>
                                <span class="admin-badge admin-badge-primary" style="font-size: 0.7rem; font-weight: 700; background: #e0f2fe; color: #0369a1;">
                                    💳 CÓ QR
                                </span>
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
