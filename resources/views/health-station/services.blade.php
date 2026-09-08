@extends('layouts.health-station')

@section('title', 'Danh mục Dịch vụ Y tế — ' . $eatery->name)
@section('header_title', 'Danh Mục Dịch Vụ Y Tế & Kỹ Thuật Phê Duyệt')

@section('content')
<style>
    .hs-card {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        padding: 24px;
        margin-bottom: 24px;
    }

    .hs-table {
        width: 100%;
        border-collapse: collapse;
    }

    .hs-table th {
        background-color: #f8fafc;
        padding: 14px 20px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .hs-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: #334155;
    }

    .hs-form-inline {
        display: grid;
        grid-template-columns: 2fr 1.5fr 3fr auto;
        gap: 12px;
        align-items: end;
    }

    .hs-control {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 0.88rem;
        font-family: inherit;
    }

    .btn-add-service {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: #ffffff;
        border: none;
        padding: 11px 20px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
    }

    .btn-action-edit {
        background-color: #f0fdf4;
        color: #0d9488;
        border: 1px solid #ccfbf1;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        margin-right: 4px;
    }
    .btn-action-edit:hover {
        background-color: #ccfbf1;
    }

    .btn-action-del {
        background-color: #fef2f2;
        color: #ef4444;
        border: 1px solid #fee2e2;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
    }

    /* Modal Styles */
    .hs-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .hs-modal.active {
        display: flex;
    }
    .hs-modal-content {
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 580px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .hs-modal-header {
        padding: 18px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .hs-modal-header h3 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }
    .hs-modal-body {
        padding: 24px;
    }
    .hs-modal-footer {
        padding: 16px 24px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
</style>

<!-- Add Form -->
<div class="hs-card">
    <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-square-plus" style="color: #0d9488;"></i> Thêm Dịch Vụ / Kỹ Thuật Y Tế Mới
    </h3>
    <form action="{{ route('health-station.services.store') }}" method="POST">
        @csrf
        <div class="hs-form-inline">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Tên Kỹ Thuật / Dịch Vụ</label>
                <input type="text" name="name" class="hs-control" required placeholder="e.g. Sơ Cấp Cứu Ban Đầu 24/7">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Chế Độ Khám & BHYT</label>
                <input type="text" name="price" class="hs-control" placeholder="e.g. Khám BHYT / Miễn phí">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Mô Tả Chi Tiết Nhiệm Vụ</label>
                <input type="text" name="description" class="hs-control" placeholder="Mô tả phạm vi hỗ trợ và tiếp nhận bệnh nhân...">
            </div>
            <div>
                <button type="submit" class="btn-add-service">
                    <i class="fa-solid fa-plus"></i> Thêm Dịch Vụ
                </button>
            </div>
        </div>
    </form>
</div>

<!-- List Services -->
<div class="hs-card" style="padding: 0; overflow: hidden;">
    <table class="hs-table">
        <thead>
            <tr>
                <th style="width: 60px;">STT</th>
                <th>Tên Dịch Vụ / Kỹ Thuật</th>
                <th>Chi Phí & Khám BHYT</th>
                <th>Mô Tả & Quy Trình</th>
                <th style="width: 140px; text-align: center;">Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $index => $srv)
            <tr>
                <td style="font-weight: 800; color: #64748b;">#{{ $index + 1 }}</td>
                <td>
                    <strong style="color: #0f172a;">{{ $srv->name }}</strong>
                </td>
                <td>
                    <span style="background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 50px; font-weight: 700; font-size: 0.78rem;">
                        {{ $srv->price }}
                    </span>
                </td>
                <td style="color: #64748b; font-size: 0.85rem;">
                    {{ $srv->description ?? 'Đã được phê duyệt trong danh mục kỹ thuật y tế cơ sở' }}
                </td>
                <td style="text-align: center;">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <button type="button" class="btn-action-edit" onclick='openEditServiceModal(@json($srv))'>
                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                        </button>
                        <form action="{{ route('health-station.services.destroy', $srv->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action-del">
                                <i class="fa-solid fa-trash"></i> Xóa
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b; padding: 36px;">
                    Chưa có dịch vụ y tế nào được tạo. Hãy điền mẫu phía trên để thêm mới!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Edit Service -->
<div id="editServiceModal" class="hs-modal">
    <div class="hs-modal-content">
        <form id="editServiceForm" method="POST">
            @csrf
            @method('PUT')
            <div class="hs-modal-header">
                <h3><i class="fa-solid fa-pen-to-square" style="color: #0d9488;"></i> Cập Nhật Dịch Vụ Y Tế & Kỹ Thuật</h3>
                <button type="button" onclick="closeEditServiceModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;">&times;</button>
            </div>
            <div class="hs-modal-body">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Tên Dịch Vụ / Kỹ Thuật *</label>
                    <input type="text" id="edit_srv_name" name="name" class="hs-control" required>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Chế Độ Khám & BHYT</label>
                    <input type="text" id="edit_srv_price" name="price" class="hs-control">
                </div>
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Mô Tả Chi Tiết Nhiệm Vụ & Phụ Trách</label>
                    <textarea id="edit_srv_description" name="description" rows="3" class="hs-control"></textarea>
                </div>
            </div>
            <div class="hs-modal-footer">
                <button type="button" onclick="closeEditServiceModal()" style="padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-weight: 700; cursor: pointer;">Hủy Bỏ</button>
                <button type="submit" class="btn-add-service" style="padding: 10px 20px;">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditServiceModal(srv) {
    document.getElementById('editServiceForm').action = "/health-station/services/" + srv.id;
    document.getElementById('edit_srv_name').value = srv.name || '';
    document.getElementById('edit_srv_price').value = srv.price || '';
    document.getElementById('edit_srv_description').value = srv.description || '';
    document.getElementById('editServiceModal').classList.add('active');
}

function closeEditServiceModal() {
    document.getElementById('editServiceModal').classList.remove('active');
}
</script>
@endsection
