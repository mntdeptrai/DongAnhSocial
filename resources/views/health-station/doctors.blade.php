@extends('layouts.health-station')

@section('title', 'Đội ngũ Bác sĩ & Lịch trực — ' . $eatery->name)
@section('header_title', 'Quản Lý Đội Ngũ Bác Sĩ & Lịch Trực Ban KCB')

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

    .hs-form-grid {
        display: grid;
        grid-template-columns: 1.5fr 1.2fr 1.5fr 1.5fr 1fr auto;
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

    .btn-add-doctor {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        border: none;
        padding: 11px 20px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.88rem;
        cursor: pointer;
    }

    .doc-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #0284c7;
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
        max-width: 600px;
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
        <i class="fa-solid fa-user-plus" style="color: #0284c7;"></i> Thêm Bác Sĩ / Cán Bộ Trực Ban Mới
    </h3>
    <form action="{{ route('health-station.doctors.store') }}" method="POST">
        @csrf
        <div class="hs-form-grid">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Họ & Tên Cán Bộ *</label>
                <input type="text" name="name" class="hs-control" required placeholder="e.g. BS. Nguyễn Thu Hà">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Chức Vụ</label>
                <input type="text" name="title" class="hs-control" placeholder="e.g. Trưởng Khoa / Giám đốc">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Chuyên Khoa Phụ Trách</label>
                <input type="text" name="specialty" class="hs-control" placeholder="e.g. Khám Chữa Bệnh BHYT & Cấp Cứu">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Lịch Trực Khám</label>
                <input type="text" name="duty_schedule" class="hs-control" placeholder="e.g. Thứ 2 - Thứ 6 (07:30 - 17:00)">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Số Điện Thoại</label>
                <input type="text" name="phone" class="hs-control" placeholder="SĐT cán bộ">
            </div>
            <div>
                <button type="submit" class="btn-add-doctor">
                    <i class="fa-solid fa-plus"></i> Thêm Cán Bộ
                </button>
            </div>
        </div>
    </form>
</div>

<!-- List Doctors -->
<div class="hs-card" style="padding: 0; overflow: hidden;">
    <table class="hs-table">
        <thead>
            <tr>
                <th style="width: 70px;">Chân Dung</th>
                <th>Họ & Tên Cán Bộ</th>
                <th>Chức Vụ / Chuyên Khoa</th>
                <th>Lịch Trực Khám Trong Tuần</th>
                <th>SĐT Trực Tiếp</th>
                <th style="width: 140px; text-align: center;">Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($doctors as $doc)
            <tr>
                <td>
                    <img src="{{ $doc->avatar }}" class="doc-avatar" alt="{{ $doc->name }}">
                </td>
                <td>
                    <strong style="color: #0f172a; font-size: 0.95rem;">{{ $doc->name }}</strong>
                </td>
                <td>
                    <div style="font-weight: 700; color: #0f766e;">{{ $doc->title }}</div>
                    <div style="font-size: 0.78rem; color: #64748b;">{{ $doc->specialty }}</div>
                </td>
                <td>
                    <span style="background-color: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; color: #334155;">
                        <i class="fa-solid fa-calendar-check" style="color: #0d9488;"></i> {{ $doc->duty_schedule }}
                    </span>
                </td>
                <td style="font-weight: 700; color: #0f172a;">
                    <i class="fa-solid fa-phone" style="color: #10b981;"></i> {{ $doc->phone }}
                </td>
                <td style="text-align: center;">
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <button type="button" class="btn-action-edit" onclick='openEditDoctorModal(@json($doc))'>
                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                        </button>
                        <form action="{{ route('health-station.doctors.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bác sĩ này khỏi danh sách trực?')" style="margin: 0;">
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
                <td colspan="6" style="text-align: center; color: #64748b; padding: 36px;">
                    Chưa có danh sách bác sĩ trực. Điền mẫu phía trên để thêm mới!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Edit Doctor -->
<div id="editDoctorModal" class="hs-modal">
    <div class="hs-modal-content">
        <form id="editDoctorForm" method="POST">
            @csrf
            @method('PUT')
            <div class="hs-modal-header">
                <h3><i class="fa-solid fa-user-pen" style="color: #0284c7;"></i> Cập Nhật Thông Tin Bác Sĩ / Cán Bộ</h3>
                <button type="button" onclick="closeEditDoctorModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;">&times;</button>
            </div>
            <div class="hs-modal-body">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Họ & Tên Cán Bộ *</label>
                    <input type="text" id="edit_doc_name" name="name" class="hs-control" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Chức Vụ</label>
                        <input type="text" id="edit_doc_title" name="title" class="hs-control">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Chuyên Khoa Phụ Trách</label>
                        <input type="text" id="edit_doc_specialty" name="specialty" class="hs-control">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Lịch Trực Khám</label>
                        <input type="text" id="edit_doc_duty_schedule" name="duty_schedule" class="hs-control">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Số Điện Thoại Liên Hệ</label>
                        <input type="text" id="edit_doc_phone" name="phone" class="hs-control">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 4px;">Đường Dẫn Ảnh Chân Dung (Direct Image URL)</label>
                    <input type="text" id="edit_doc_avatar" name="avatar" class="hs-control">
                </div>
            </div>
            <div class="hs-modal-footer">
                <button type="button" onclick="closeEditDoctorModal()" style="padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-weight: 700; cursor: pointer;">Hủy Bỏ</button>
                <button type="submit" class="btn-add-doctor" style="padding: 10px 20px;">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditDoctorModal(doc) {
    document.getElementById('editDoctorForm').action = "/health-station/doctors/" + doc.id;
    document.getElementById('edit_doc_name').value = doc.name || '';
    document.getElementById('edit_doc_title').value = doc.title || '';
    document.getElementById('edit_doc_specialty').value = doc.specialty || '';
    document.getElementById('edit_doc_duty_schedule').value = doc.duty_schedule || '';
    document.getElementById('edit_doc_phone').value = doc.phone || '';
    document.getElementById('edit_doc_avatar').value = doc.avatar || '';
    document.getElementById('editDoctorModal').classList.add('active');
}

function closeEditDoctorModal() {
    document.getElementById('editDoctorModal').classList.remove('active');
}
</script>
@endsection
