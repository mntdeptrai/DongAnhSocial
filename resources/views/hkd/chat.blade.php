@extends('layouts.hkd')

@section('title', 'Kênh Trò Chuyện Khách Hàng — ' . $eatery->name)
@section('title_header', 'Trò Chuyện & Nhắn Tin Tư Vấn Khách Hàng')

@section('content')

<style>
    .hkd-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; margin-bottom: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .hkd-card-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; }
</style>

<div class="hkd-card">
    <div class="hkd-card-title">
        <i class="fa-solid fa-comments" style="color: #059669;"></i>
        <span>Kênh Trò Chuyện & Nhắn Tin Trực Tiếp</span>
    </div>

    <div style="text-align: center; padding: 60px 20px; color: #64748b;">
        <div style="width: 80px; height: 80px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
            <i class="fa-solid fa-comments" style="font-size: 2.5rem; color: #059669;"></i>
        </div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Kênh Nhắn Tin Khách Hàng Sẵn Sàng</h3>
        <p style="max-width: 500px; margin: 0 auto; font-size: 0.9rem; line-height: 1.6;">
            Khi khách hàng bấm vào nút "Trò chuyện với chủ cơ sở" trên trang công khai địa điểm, tin nhắn sẽ hiển thị tại đây để bạn tư vấn và chốt đơn tức thì.
        </p>
    </div>
</div>

@endsection
