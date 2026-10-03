@extends('layouts.app')

@php
    $pageTitle = 'Khám phá Đông Anh - Nền tảng Số hóa Dịch vụ & Du lịch Xã Đông Anh';
    $pageDesc = 'Khám phá Xã Đông Anh - Tra cứu chính xác các trường học, bệnh viện, nhà hàng quán ăn, đặc sản OCOP, chợ truyền thống tại Xã Đông Anh, Hà Nội.';
    $pageOgImage = 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f5fa_fe0f/512.png'; // Bản đồ 🗺️ (mặc định)

    if (isset($selectedCatSlug)) {
        $cleanCatSlug = trim($selectedCatSlug, "/'\" ");
        if ($cleanCatSlug === 'smart-education-map') {
            $pageTitle = 'Bản đồ Giáo dục thông minh Đông Anh - Tra cứu Trường học chuẩn hóa';
            $pageDesc = 'Bản đồ Giáo dục thông minh Xã Đông Anh: Tra cứu thông tin chi tiết các trường mầm non, tiểu học, THCS, THPT, quy mô lớp học, học sinh và đề án sáp nhập trường lớp.';
            $pageOgImage = 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f393/512.png'; // Mũ cử nhân 🎓
        } elseif ($cleanCatSlug === 'dong-anh-market') {
            $pageTitle = 'Nông sản sạch & Đặc sản OCOP Đông Anh - Chợ Số Truyền Thống';
            $pageDesc = 'Gian hàng nông sản và đặc sản OCOP Xã Đông Anh: Bản đồ số các chợ, danh sách sản phẩm OCOP đạt chuẩn 3 sao, 4 sao và thông tin nhà sản xuất uy tín.';
            $pageOgImage = 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f33e/512.png'; // Bông lúa 🌾
        } elseif ($cleanCatSlug === 'dong-anh-food-map') {
            $pageTitle = 'Bản đồ Ẩm thực & Quán ngon nổi bật Đông Anh';
            $pageDesc = 'Bản đồ Ẩm thực Đông Anh: Khám phá danh sách nhà hàng, quán ăn ngon nổi tiếng, ẩm thực đặc trưng của vùng đất Đông Anh, Hà Nội.';
            $pageOgImage = 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f35c/512.png'; // Bát mì 🍜
        } elseif ($cleanCatSlug === 'stay-in-dong-anh') {
            $pageTitle = 'Khách sạn, Homestay & Dịch vụ Lưu trú Đông Anh';
            $pageDesc = 'Cơ sở lưu trú dịch vụ Đông Anh: Tìm kiếm và đặt phòng khách sạn, homestay chất lượng tốt tại Đông Anh, Hà Nội kèm theo hướng dẫn đường đi.';
            $pageOgImage = 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f6cc/512.png'; // Giường ngủ 🛌
        } elseif ($cleanCatSlug === 'wellness-care') {
            $pageTitle = 'Bệnh viện, Phòng khám & Dịch vụ Y tế Đông Anh';
            $pageDesc = 'Hệ thống y tế & Chăm sóc sức khỏe Đông Anh: Tra cứu nhanh bệnh viện, trạm y tế, spa chăm sóc da, phòng khám đa khoa uy tín chất lượng.';
            $pageOgImage = 'https://fonts.gstatic.com/s/e/notoemoji/latest/1fa7a/512.png'; // Ống nghe y tế 🩺
        } elseif ($cleanCatSlug === 'discover-dong-anh-community-culture-hub' || $cleanCatSlug === 'hanh-trinh-di-san') {
            $pageTitle = 'Hành trình Di sản Lịch sử & Thiết chế Văn hóa Đông Anh';
            $pageDesc = 'Khám phá văn hóa Đông Anh: Điểm đến di tích lịch sử Cổ Loa, các đền chùa lễ hội truyền thống cổ xưa và nhà văn hóa cộng đồng Đông Anh.';
            $pageOgImage = 'https://fonts.gstatic.com/s/e/notoemoji/latest/1f3db_fe0f/512.png'; // Tòa nhà cổ kính 🏛️
        }
    }
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDesc)
@section('og_image', $pageOgImage)
@section('canonical_url', request()->fullUrl())

@section('content')
    @include('home.partials.hero-banner')
    @include('home.partials.category-nav')
    @include('home.partials.eateries-list')
@endsection

@section('scripts')
    @include('home.partials.home-scripts')
@endsection
