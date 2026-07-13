<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <script>
        // Force light theme
        document.documentElement.setAttribute('data-theme', 'light');
    </script>
    
    <!-- SEO Meta Tags -->
    <title><?php echo $__env->yieldContent('title', 'Bản đồ số Đông Anh - Dong Anh Map'); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'Bản đồ số Đông Anh - Số hóa toàn bộ trường học, bệnh viện, khách sạn, nhà hàng, quán ăn và đặc sản tại Đông Anh. Chỉ đường nhanh, thông tin chi tiết, liên kết Google Maps.'); ?>">
    <meta name="keywords" content="bản đồ đông anh, trường học đông anh, bệnh viện đông anh, đặc sản đông anh, khách sạn đông anh, địa điểm đông anh">
    <link rel="canonical" href="<?php echo $__env->yieldContent('canonical_url', request()->url()); ?>">
    
    <!-- OpenGraph Social Tags -->
    <meta property="og:site_name" content="Dong Anh Map">
    <meta property="og:title" content="<?php echo $__env->yieldContent('title', 'Bản đồ số Đông Anh - Dong Anh Map'); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('meta_description', 'Bản đồ số Đông Anh - Số hóa trường học, bệnh viện, khách sạn, nhà hàng và đặc sản.'); ?>">
    <meta property="og:image" content="<?php echo $__env->yieldContent('og_image', 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80'); ?>">
    <meta property="og:type" content="website">
    
    <!-- Leaflet.js Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <!-- Google Fonts Preload: Outfit (heading) + Be Vietnam Pro (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Custom Theme Styling -->
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=<?php echo e(file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1.0.0'); ?>">
    
    <!-- Mobile Native Overrides (Only load for mobile and high zoom desktops) -->
    <link rel="stylesheet" media="screen and (max-width: 1200px)" href="<?php echo e(asset('css/mobile-native.css')); ?>?v=<?php echo e(file_exists(public_path('css/mobile-native.css')) ? filemtime(public_path('css/mobile-native.css')) : '1.0.0'); ?>">

    <!-- 📱 Mobile Viewport Fit Fix — Ôm gọn nội dung, không scroll ngang -->
    <link rel="stylesheet" href="<?php echo e(asset('css/mobile-fix.css')); ?>?v=<?php echo e(file_exists(public_path('css/mobile-fix.css')) ? filemtime(public_path('css/mobile-fix.css')) : '1.0.0'); ?>">
    
    <!-- Dynamic Schema.org JSON-LD Structured Data for Google Indexing -->
    <?php echo $__env->yieldContent('seo_schema'); ?>
    
    <!-- Alpine.js for Modern Client Interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- 📱 MOBILE LAYOUT — Safety net + box-sizing universal -->
    <style>
        /* Box-sizing universal (nếu base.css chưa load kịp) */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        /* Safety net: body direct children không vượt 100vw */
        body > * {
            max-width: 100%;
        }

        @media (max-width: 992px) {
            html {
                /* html { overflow-x: hidden } đặt trong base.css */
                overflow-y: auto;
                height: auto;
            }
            body {
                /* KHÔNG đặt overflow-x: hidden ở body — gây iOS scroll bug */
                overflow-y: auto;
                height: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>

    <!-- Sticky Glass Navigation Header -->
    <header class="glass-nav">
        <div class="container nav-wrapper">
            <a href="/" class="logo">
                <span>🗺️</span> DongAnh Map Discovery
            </a>
            
            <div class="nav-collapse main-nav-container" id="navCollapse">
                <nav>
                <ul class="nav-menu">
                    <li><a href="/" class="nav-link <?php echo e(request()->is('/') && !request()->has('cat') ? 'active' : ''); ?>">Trang chủ</a></li>
                    <li><a href="/tim-kiem" class="nav-link <?php echo e(request()->is('tim-kiem*') ? 'active' : ''); ?>">Bản đồ & Tìm kiếm</a></li>
                    <li><a href="/food-tours" class="nav-link <?php echo e(request()->is('food-tours*') || (request()->is('food-tour*') && !request()->is('food-tour/tu-tay-lam-dac-san-co-loa*')) ? 'active' : ''); ?>">Food Tour</a></li>
                    <li><a href="/exp-corner" class="nav-link <?php echo e(request()->is('exp-corner*') || request()->is('food-tour/tu-tay-lam-dac-san-co-loa*') ? 'active' : ''); ?>">Góc trải nghiệm thực tế</a></li>
                    <li><a href="/checkin" class="nav-link <?php echo e(request()->is('checkin*') ? 'active' : ''); ?>">Góc Check-in</a></li>
                    <?php if(session()->has('user_id')): ?>
                        <li><a href="/social" class="nav-link <?php echo e(request()->is('social*') ? 'active' : ''); ?>">💬 Kết nối bạn bè</a></li>
                    <?php endif; ?>
                    <li><a href="#" onclick="openGuideModal(event)" class="nav-link">Giới thiệu & Hướng dẫn</a></li>
                    <?php if(session('user_role') === 'admin'): ?>
                        <li><a href="/admin/dashboard" class="nav-link <?php echo e(request()->is('admin*') ? 'active' : ''); ?>">Trang quản trị</a></li>
                    <?php elseif(session('user_role') === 'seller'): ?>
                        <li><a href="/admin/dashboard" class="nav-link <?php echo e(request()->is('admin*') ? 'active' : ''); ?>">Quản lý quán</a></li>
                    <?php endif; ?>
                </ul>
                </nav>
            
                <div class="user-actions">
                    <?php if(session()->has('user_id')): ?>
                        <div class="profile-dropdown" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="profile-trigger-btn">
                                <?php $navUser = Auth::user() ?? \App\Models\User::find(session('user_id')); ?>
                                <?php if($navUser && $navUser->avatar && str_starts_with($navUser->avatar, 'avatars/')): ?>
                                    <img src="<?php echo e(rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $navUser->avatar); ?>" alt="avatar" class="avatar-icon" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(var(--primary-rgb),0.3);">
                                <?php else: ?>
                                    <span class="avatar-icon"><?php echo e($navUser->avatar ?? '👤'); ?></span>
                                <?php endif; ?>
                                <span class="user-display-name"><?php echo e(session('user_name')); ?></span>
                                <svg class="chevron-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="width: 12px; height: 12px; min-width: 12px; min-height: 12px; display: inline-block; transition: transform 0.2s;" :style="open ? 'transform: rotate(180deg)' : 'transform: rotate(0deg)'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="profile-dropdown-menu">
                                <div class="user-info-header">
                                    <div class="user-name"><?php echo e(session('user_name')); ?></div>
                                    <div class="user-role">
                                        <?php if(session('user_role') === 'admin'): ?>
                                            🏛️ Quản trị viên
                                        <?php elseif(session('user_role') === 'seller'): ?>
                                            🏪 Chủ cơ sở kinh doanh
                                        <?php else: ?>
                                            👤 Thành viên cộng đồng
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if(session('user_role') === 'admin' || session('user_role') === 'seller'): ?>
                                    <a href="/admin/dashboard" class="dropdown-item">
                                        <span>📊</span> Trang quản lý
                                    </a>
                                <?php endif; ?>
                                
                                <a href="/profile" class="dropdown-item">
                                    <span>👤</span> Trang cá nhân
                                </a>
                                
                                <form action="/auth/logout" method="POST" style="margin: 0; width: 100%;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="dropdown-item dropdown-item-logout">
                                        <span>🚪</span> Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/auth/login" class="btn-secondary" style="text-decoration: none; padding: 6px 14px; font-size: 0.85rem; border-radius: 8px;">Đăng nhập</a>
                        <a href="/auth/register" class="btn-primary" style="text-decoration: none; padding: 6px 14px; font-size: 0.85rem; border-radius: 8px;">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </div> <!-- End nav-collapse -->

            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3 class="logo" style="margin-bottom: 16px; font-size: 1.3rem;">🗺️ Dong Anh Map</h3>
                    <p style="font-size: 0.85rem; line-height: 1.6; max-width: 480px;">
                        Bản đồ số Đông Anh là giải pháp công nghệ số hóa toàn bộ trường học, bệnh viện, cơ sở y tế, khách sạn, nhà nghỉ, nhà hàng, quán cafe và quảng bá các sản phẩm OCOP đặc sản truyền thống của huyện Đông Anh, Hà Nội. Hỗ trợ chuyển đổi số và nâng tầm văn hóa du lịch địa phương.
                    </p>
                </div>
                <div>
                    <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem;">Liên kết nhanh</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px; font-size: 0.85rem;">
                        <li><a href="/" style="hover: color: var(--primary);">Trang chủ</a></li>
                        <li><a href="/tim-kiem" style="hover: color: var(--primary);">Bản đồ số</a></li>
                        <li><a href="#" onclick="openGuideModal(event)" style="hover: color: var(--primary);">Giới thiệu & Hướng dẫn</a></li>
                        <li><a href="/auth/login" style="hover: color: var(--primary);">Đăng nhập quản trị viên</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: var(--text-main); margin-bottom: 16px; font-size: 1rem;">Thông tin hành chính</h4>
                    <p style="font-size: 0.85rem; line-height: 1.6;">
                        <strong>Cơ quan chủ quản:</strong> Uỷ ban nhân dân xã Đông Anh<br>
                        <strong>Chịu trách nhiệm chính:</strong> Phòng Văn hóa - Xã hội xã Đông Anh<br>
                        📍 Địa chỉ: Số 66 đường Cao Lỗ, xã Đông Anh, thành phố Hà Nội<br>
                        📞 Điện thoại: 0243.965.2973<br>
                        🌐 Website: <a href="https://donganh.hanoi.gov.vn" target="_blank" style="color: inherit; text-decoration: none;">donganh.hanoi.gov.vn</a>
                    </p>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.3);">
                &copy; 2026 Bản đồ số Đông Anh (Dong Anh Map). Tất cả quyền được bảo lưu. Phát triển bởi Phòng Văn hóa - Xã hội xã Đông Anh
            </div>
        </div>
    </footer>

    <!-- Leaflet.js Map Library -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Hamburger Menu Logic
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const navCollapse = document.getElementById('navCollapse');
            
            if (mobileMenuBtn && navCollapse) {
                mobileMenuBtn.addEventListener('click', function() {
                    this.classList.toggle('open');
                    navCollapse.classList.toggle('show');
                });
            }

            // Scroll-triggered animations with Intersection Observer
            const observerOptions = {
                root: null,
                rootMargin: "0px",
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            revealElements.forEach(el => observer.observe(el));

            // Button Ripple Micro-interaction
            document.addEventListener('click', function(e) {
                const target = e.target.closest('.btn-primary, .btn-secondary, .btn-accent, .category-card');
                if (target) {
                    const rect = target.getBoundingClientRect();
                    const ripple = document.createElement('span');
                    ripple.className = 'ripple-effect';
                    
                    const size = Math.max(rect.width, rect.height);
                    ripple.style.width = ripple.style.height = `${size}px`;
                    
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    target.style.position = 'relative';
                    target.style.overflow = 'hidden';
                    target.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                }
            });

            // Smooth Scroll state triggers for Nav and Parallax
            const handleScroll = () => {
                const nav = document.querySelector('.glass-nav');
                if (nav) {
                    if (window.scrollY > 20) {
                        nav.classList.add('scrolled');
                    } else {
                        nav.classList.remove('scrolled');
                    }
                }
                // Update parallax css variable
                document.documentElement.style.setProperty('--scroll-offset', window.scrollY);
            };

            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll(); // Run once on startup
        });

        window.openGuideModal = function(e) {
            if (e) e.preventDefault();
            const modal = document.getElementById('guideModal');
            if (modal) {
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.style.opacity = '1';
                    modal.querySelector('.lightbox-content').style.transform = 'scale(1)';
                }, 10);
            }
        };

        window.closeGuideModal = function() {
            const modal = document.getElementById('guideModal');
            if (modal) {
                modal.style.opacity = '0';
                modal.querySelector('.lightbox-content').style.transform = 'scale(0.9)';
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        };
    </script>
    
    <!-- Beautiful Guide & Introduction Modal -->
    <div id="guideModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(12px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div class="lightbox-content" style="background: var(--bg-card); border: 1px solid var(--border-glow); width: 90%; max-width: 650px; max-height: 85vh; border-radius: 24px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5); overflow: hidden; transform: scale(0.9); transition: transform 0.3s ease; display: flex; flex-direction: column; position: relative;">
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border-glow); padding: 20px 24px; background: rgba(255,255,255,0.01);">
                <h3 style="margin: 0; font-size: 1.4rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px; font-family: var(--font-heading);">
                    ℹ️ Giới thiệu & Hướng dẫn sử dụng
                </h3>
                <button onclick="closeGuideModal()" style="background: transparent; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">✕</button>
            </div>
            
            <!-- Modal Content (Scrollable) -->
            <div style="overflow-y: auto; padding: 24px 28px; flex: 1; display: flex; flex-direction: column; gap: 24px; line-height: 1.6; color: var(--text-muted);">
                <!-- Section 1: Giới thiệu Đông Anh -->
                <div>
                    <h4 style="color: var(--text-main); font-size: 1.1rem; margin-top: 0; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        🌾 Giới thiệu về Đông Anh
                    </h4>
                    <p style="font-size: 0.9rem; margin: 0;">
                        Đông Anh là vùng đất địa linh nhân kiệt có bề dày lịch sử và truyền thống văn hóa lâu đời, gắn liền với di tích Cổ Loa thành. Hiện nay, Đông Anh đang chuyển mình mạnh mẽ trong tiến trình đô thị hóa và chuyển đổi số. Bản đồ số Đông Anh ra đời nhằm cung cấp giải pháp số hóa toàn diện các hạ tầng dịch vụ: trường học, y tế, lưu trú, ẩm thực địa phương và các sản phẩm OCOP đặc trưng của huyện, hỗ trợ nâng cao đời sống dân cư và thúc đẩy phát triển du lịch bền vững.
                    </p>
                </div>
                
                <!-- Section 2: Hướng dẫn sử dụng -->
                <div>
                    <h4 style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        🗺️ Hướng dẫn sử dụng bản đồ
                    </h4>
                    
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div style="display: flex; gap: 12px;">
                            <span style="font-size: 1.2rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">1</span>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">Tìm kiếm địa điểm</strong>
                                <span style="font-size: 0.85rem;">Nhập từ khóa như tên trường học, bệnh viện, khách sạn, món ăn tại ô tìm kiếm ở trang chủ để hiển thị vị trí trên bản đồ vệ tinh.</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 12px;">
                            <span style="font-size: 1.2rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">2</span>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">Lọc nâng cao & Bán kính GPS</strong>
                                <span style="font-size: 0.85rem;">Trong phần "Bản đồ & Tìm kiếm", bạn có thể kích hoạt định vị GPS thực tế để quét các tiện ích xung quanh mình trong phạm vi từ 1km đến 10km.</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 12px;">
                            <span style="font-size: 1.2rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">3</span>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">Góc trải nghiệm thực tế</strong>
                                <span style="font-size: 0.85rem;">Tham gia các lớp học nghề, hoạt động vui chơi giải trí và trải nghiệm thực tế các ngành nghề, văn hóa truyền thống Đông Anh.</span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <span style="font-size: 1.2rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: bold;">4</span>
                            <div>
                                <strong style="color: var(--text-main); display: block; font-size: 0.9rem;">Gửi phản hồi chất lượng dịch vụ</strong>
                                <span style="font-size: 0.85rem;">Nếu phát hiện thông tin địa điểm không chính xác hoặc dịch vụ không đạt chất lượng/an toàn vệ sinh, hãy nhấn "Báo cáo / Góp ý" trực tiếp tại trang chi tiết để gửi thông tin ẩn danh đến Ban quản lý.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="border-top: 1px dashed var(--border-glow); padding: 16px 24px; background: rgba(255,255,255,0.01); display: flex; justify-content: flex-end;">
                <button onclick="closeGuideModal()" class="btn-primary" style="font-size: 0.85rem; padding: 8px 20px; border-radius: 8px; cursor: pointer;">Đã hiểu</button>
            </div>
        </div>
    </div>
    
    <?php echo $__env->yieldContent('scripts'); ?>

    <?php if(session()->has('user_id')): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function sendHeartbeat() {
                fetch('/user/heartbeat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                }).catch(function(err) {
                    console.warn('Heartbeat update skipped', err);
                });
            }
            sendHeartbeat();
            setInterval(sendHeartbeat, 60000); // 1 minute
        });
    </script>
    <?php endif; ?>

    
    <?php echo $__env->yieldPushContent('realtime-scripts'); ?>
</body>
</html>
<?php /**PATH /home/mnt/Downloads/FOOD_MAP/resources/views/layouts/app.blade.php ENDPATH**/ ?>