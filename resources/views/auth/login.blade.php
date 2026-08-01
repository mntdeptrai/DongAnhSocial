@extends('layouts.app')

@section('title', 'Đăng nhập - Bản đồ số Khám phá Đông Anh')

@section('content')
<div style="position: relative; overflow: hidden; background: var(--bg-base); min-height: calc(100vh - var(--header-height) - 160px); display: flex; align-items: center; justify-content: center; width: 100%;">
    <!-- Glowing atmosphere orbs -->
    <div class="cinematic-glow-orb-1" style="top: 10%; left: 20%; width: 350px; height: 350px;"></div>
    <div class="cinematic-glow-orb-2" style="bottom: 10%; right: 20%; width: 400px; height: 400px;"></div>
    
    <!-- Sparkles particles -->
    <div class="particles-container">
        <div class="particle p-1"></div>
        <div class="particle p-2" style="animation-delay: 2s;"></div>
        <div class="particle p-3" style="animation-delay: 4s;"></div>
        <div class="particle p-4" style="animation-delay: 1.5s;"></div>
    </div>
    <!-- Shooting stars meteor shower background -->
    <div class="shooting-stars-container">
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
        <div class="shooting-star"></div>
    </div>
    
    <div class="container" style="padding: 60px 0; display: flex; justify-content: center; align-items: center; position: relative; z-index: 2; width: 100%;">
        <div class="glass-panel hover-lift" style="width: 100%; max-width: 440px; padding: 40px; box-shadow: var(--shadow-overlay); border: 1px solid var(--border-glow); background: var(--bg-card); backdrop-filter: blur(20px);">
            
            <div style="text-align: center; margin-bottom: 30px;">
                <span style="font-size: 3rem; display: block; margin-bottom: 10px;">🍜</span>
                <h2 style="font-size: 1.8rem; font-family: var(--font-heading); background: var(--primary-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; padding: 6px 0; line-height: 1.3; font-weight: 800;">
                    Đăng nhập hệ thống
                </h2>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">
                    Quản lý quán ăn và cập nhật bản đồ khám phá đông anh
                </p>
            </div>
            
            <!-- Hiển thị các lỗi validation -->
            @if ($errors->any())
                <div class="glass-panel" style="background: rgba(240, 78, 35, 0.1); border-color: var(--primary-hover); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; color: var(--primary); font-size: 0.85rem;">
                    <ul style="list-style: none;">
                        @foreach ($errors->all() as $error)
                            <li>⚠️ {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="/auth/login" method="POST">
                @csrf
                
                <div class="review-form-group">
                    <label class="review-form-label" style="font-size: 0.85rem; font-weight: 600;">Tài khoản đăng nhập</label>
                    <input type="text" name="email" value="{{ old('email') }}" class="form-input" required placeholder="Email, tên đăng nhập hoặc số điện thoại" style="padding: 12px 16px; width: 100%;">
                </div>
                
                <div class="review-form-group" style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="review-form-label" style="margin-bottom: 0; font-size: 0.85rem; font-weight: 600;">Mật khẩu</label>
                        <a href="/auth/forgot-password" style="font-size: 0.8rem; color: var(--primary);">Quên mật khẩu?</a>
                    </div>
                    <input type="password" name="password" class="form-input" required placeholder="••••••••" style="padding: 12px 16px; width: 100%;">
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 12px 0; font-size: 1rem; margin-bottom: 20px;">
                    Đăng nhập ngay
                </button>
            </form>
            
            <div style="text-align: center; border-top: 1px solid var(--border-glow); padding-top: 20px; font-size: 0.85rem; color: var(--text-muted);">
                Chưa có tài khoản chủ quán? 
                <a href="/auth/register" style="color: var(--primary); font-weight: 600;">Đăng ký tại đây</a>
            </div>
            
        </div>
    </div>
</div>
@endsection
